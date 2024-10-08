<?php

declare(strict_types=1);

namespace totaldev\SchemaGenerator;

use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use PhpCsFixer\Utils;
use totaldev\SchemaGenerator\Model\ClassDefinition;

/**
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class CodeGenerator
{
    public const FUNCTION_CLASS = 'TdFunction';
    public const OBJECT_CLASS = 'TdObject';
    public const SCHEMA_REGISTRY_CLASS = 'TdSchemaRegistry';
    public const TYPE_SERIALIZER_INTERFACE = 'TdTypeSerializableInterface';

    public function __construct(
        private string $baseNamespace,
        private string $baseFolder,
    ) {
    }

    /**
     * @param ClassDefinition[] $classes
     */
    public function generate(array $classes): void
    {
        $files = [
            'TdTypeSerializableInterface.php' => $this->generateTypeSerializeInterface(),
            'TdObject.php' => $this->generateTdObject(),
            'TdFunction.php' => $this->generateTdFunction(),
            'TdSchemaRegistry.php' => $this->generateTdSchemaRegistry($classes),
        ];

        foreach ($classes as $classDefinition) {
            $subDir = $this->getClassNamespaceAdd($classDefinition->className);

            if ($subDir) {
                $subDirPath = "$this->baseFolder/$subDir";
                if (!is_dir($subDirPath) && !mkdir($subDirPath) && !is_dir($subDirPath)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $subDirPath));
                }
            }
            $fileName = ($subDir ? "$subDir/" : '') . $classDefinition->className . '.php';

            $files[$fileName] = $this->generateClass($classDefinition);
        }

        $printer = new PsrPrinter();
        $printer->setTypeResolving(false);
        foreach ($files as $fileName => $file) {
            $filePath = $this->baseFolder . '/' . $fileName;

            file_put_contents($filePath, $printer->printFile($file));
        }
    }

    public function generateClass(ClassDefinition $classDef): PhpFile
    {
        $phpFile = new PhpFile();
        $phpFile->addComment('This phpFile is auto-generated.');
        //        $phpFile->setStrictTypes(); // adds declare(strict_types=1)

        $phpNamespace = $phpFile->addNamespace(
            $this->calculateNamespace($classDef->className)
        );
        $phpNamespace->addUse($this->baseNamespace . '\\TdSchemaRegistry');

        $class = $phpNamespace->addClass($classDef->className);

        $parentClass = $classDef->parentClass;
        if ('Object' === $parentClass) {
            $phpNamespace->addUse($this->baseNamespace . '\\' . static::OBJECT_CLASS);
            $parentClass = static::OBJECT_CLASS;
        } elseif ('Function' === $parentClass) {
            $phpNamespace->addUse($this->baseNamespace . '\\' . static::FUNCTION_CLASS);
            $parentClass = static::FUNCTION_CLASS;
        }

        $class->setExtends($parentClass)
            ->addComment($classDef->classDocs);

        $class->addConstant('TYPE_NAME', $classDef->typeName)
            ->setPublic();

        $constructor = $class->addMethod('__construct')
            ->setPublic();

        if (!in_array($parentClass, [static::OBJECT_CLASS, static::FUNCTION_CLASS])) {
            $constructor->addBody('parent::__construct();')
                ->addBody('');
        }

        $fromArray = $class->addMethod('fromArray')
            ->setPublic()
            ->setStatic()
            ->setReturnType($classDef->className);
        $fromArray->addParameter('array')
            ->setType('array');

        $serialize = $class->addMethod('typeSerialize')
            ->setReturnType('array');

        if (count($classDef->fields) > 0) {
            $fromArray->addBody('return new static(');
            $serialize->addBody('return [');
            $serialize->addBody('    \'@type\' => static::TYPE_NAME,');
        } else {
            $fromArray->addBody('return new static();');
            $serialize->addBody('return [\'@type\' => static::TYPE_NAME];');
        }

        foreach ($classDef->fields as $fieldDef) {
            $typeStyle = $fieldDef->type;
            $type = $fieldDef->type;

            $arrayNestLevels = substr_count($type, '[]');
            if (1 === $arrayNestLevels) {
                $type = 'array';
                $typeStyle = 'array';
            } elseif (2 === $arrayNestLevels) {
                $type = 'array';
                $typeStyle = 'array_array';
            } elseif ($arrayNestLevels > 2) {
                throw new \InvalidArgumentException('Vector of higher than 2 lvl deep');
            }

            $class->addProperty($fieldDef->name)
                ->setProtected()
                ->setNullable($fieldDef->mayBeNull)
                ->setType($type)
                ->addComment($fieldDef->doc)
                ->addComment('')
                ->addComment('@var ' . $fieldDef->type . ($fieldDef->mayBeNull ? '|null' : ''));

            $constructor->addParameter($fieldDef->name)
                ->setType($type)
                ->setNullable($fieldDef->mayBeNull);

            $constructor->addBody('$this->' . $fieldDef->name . ' = $' . $fieldDef->name . ';');

            [$rawType] = explode('[', $fieldDef->type);

            switch ($rawType) {
                case 'string':
                case 'int':
                case 'bool':
                case 'float':
                    $fromArray->addBody('    $array[\'' . $fieldDef->rawName . '\'],');
                    $serialize->addBody('    \'' . $fieldDef->rawName . '\' => $this->' . $fieldDef->name . ',');
                    break;

                default:
                    $phpNamespace->addUse($this->calculateNamespace($rawType) . '\\' . $rawType);
                    $arg = Utils::camelCaseToUnderscore($fieldDef->name);
                    if ($fieldDef->mayBeNull) {
                        if ('array' === $typeStyle) {
                            $fromArray->addBody(
                                '    (isset($array[\'' . $arg . '\']) ? array_map(fn($x) => TdSchemaRegistry::fromArray($x), $array[\'' . $arg . '\']) : null),'
                            );

                            $serialize->addBody(
                                '    (isset($this->' . $fieldDef->name .
                                ') ? array_map(fn($x) => $x->typeSerialize(), $this->' . $fieldDef->name . ') : null),'
                            );
                        } elseif ('array_array' === $typeStyle) {
                            $fromArray->addBody(
                                '    (isset($array[\'' . $arg . '\']) ? array_map(fn($x) => '
                                . 'array_map(fn($y) => TdSchemaRegistry::fromArray($y), $x), $array[\'' . $arg . '\']) : null),'
                            );

                            $serialize->addBody(
                                '    (isset($this->' . $fieldDef->name . ') ? array_map(fn($x) => array_map(fn($y) => $y->typeSerialize(), $x), $this->'
                                . $fieldDef->name . ') : null),'
                            );
                        } else {
                            $fromArray->addBody(
                                '    (isset($array[\'' . $arg . '\']) ? ' .
                                'TdSchemaRegistry::fromArray($array[\'' . $arg . '\']) : null),'
                            );

                            $serialize->addBody(
                                '    \'' . $fieldDef->rawName . '\' => (isset($this->' .
                                $fieldDef->name . ') ? $this->' . $fieldDef->name . ' : null),'
                            );
                        }
                    } else {
                        if ('array' === $typeStyle) {
                            $fromArray->addBody(
                                '    array_map(fn($x) => TdSchemaRegistry::fromArray($x), $array[\'' . $arg . '\']),'
                            );

                            $serialize->addBody(
                                '    array_map(fn($x) => $x->typeSerialize(), $this->' . $fieldDef->name . '),'
                            );
                        } elseif ('array_array' === $typeStyle) {
                            $fromArray->addBody(
                                '    array_map(fn($x) => array_map(fn($y) => TdSchemaRegistry::fromArray($y), $x), $array[\'' . $arg . '\']),'
                            );

                            $serialize->addBody(
                                '    array_map(fn($x) => array_map(fn($y) => $y->typeSerialize(), $x), $this->' . $fieldDef->name . '),'
                            );
                        } else {
                            $fromArray->addBody(
                                '    TdSchemaRegistry::fromArray($array[\'' . $arg . '\']),'
                            );

                            $serialize->addBody(
                                '    \'' . $arg . '\' => $this->' . $fieldDef->name . '->typeSerialize(),'
                            );
                        }
                    }
            }

            $getter = $class->addMethod('get' . ucfirst($fieldDef->name))
                ->setPublic()
                ->setReturnType($type)
                ->setReturnNullable($fieldDef->mayBeNull);

            $getter->addBody('return $this->' . $fieldDef->name . ';');
        }

        if (count($classDef->fields) > 0) {
            $fromArray->addBody(');');

            $serialize->addBody('];');
        }

        return $phpFile;
    }

    public function generateTdFunction(): PhpFile
    {
        $phpFile = new PhpFile();
        $phpFile->addComment('This phpFile is auto-generated.');
        // $phpFile->setStrictTypes(); // adds declare(strict_types=1)

        $phpNamespace = $phpFile->addNamespace($this->baseNamespace);

        $functionClass = $phpNamespace->addClass(static::FUNCTION_CLASS);
        $functionClass->setExtends(static::OBJECT_CLASS)
            ->setAbstract();

        return $phpFile;
    }

    public function generateTdObject(): PhpFile
    {
        $phpFile = new PhpFile();
        $phpFile->addComment('This phpFile is auto-generated.');
        // $phpFile->setStrictTypes(); // adds declare(strict_types=1)

        $phpNamespace = $phpFile->addNamespace($this->baseNamespace);
        $phpNamespace->addUse(\JsonSerializable::class);

        $objectClass = $phpNamespace->addClass(static::OBJECT_CLASS);
        $objectClass->addImplement(static::TYPE_SERIALIZER_INTERFACE)
            ->addImplement(\JsonSerializable::class)
            ->setAbstract();

        $objectClass->addConstant('TYPE_NAME', '_tdObject')
            ->setPublic();

        $objectClass->addProperty('tdExtra', new Literal('null'))
            ->setType('string')
            ->setNullable(true);

        $extraGetMethod = $objectClass->addMethod('getTdExtra')
            ->setPublic()
            ->setReturnType('string')
            ->setReturnNullable();

        $objectClass->addMethod('getTdTypeName')
            ->setPublic()
            ->setReturnType('string')
            ->setBody('return static::TYPE_NAME;');

        $extraGetMethod->addBody('return $this->tdExtra;');

        $extraSetMethod = $objectClass->addMethod('setTdExtra')
            ->setPublic()
            ->setReturnType('self');

        $extraSetMethod->addParameter('tdExtra')
            ->setType('string')
            ->setNullable();

        $extraSetMethod->addBody('$this->tdExtra = $tdExtra;');
        $extraSetMethod->addBody('');
        $extraSetMethod->addBody('return $this;');

        $jsonSerializeMethod = $objectClass->addMethod('jsonSerialize')
            ->setPublic()
            ->setReturnType('array');

        $jsonSerializeMethod->addBody('$output = [];');
        $jsonSerializeMethod->addBody('if (null !== $this->tdExtra) {');
        $jsonSerializeMethod->addBody('    $output[\'@extra\'] = $this->tdExtra;');
        $jsonSerializeMethod->addBody('}');
        $jsonSerializeMethod->addBody('');
        $jsonSerializeMethod->addBody('return array_merge($output, $this->typeSerialize());');

        return $phpFile;
    }

    /**
     * @param ClassDefinition[] $classes
     */
    public function generateTdSchemaRegistry(array $classes): PhpFile
    {
        $phpFile = new PhpFile();
        $phpFile->addComment('This phpFile is auto-generated.');
        // $phpFile->setStrictTypes(); // adds declare(strict_types=1)

        $phpNamespace = $phpFile->addNamespace($this->baseNamespace);

        $phpNamespace->addUse(\InvalidArgumentException::class);

        $class = $phpNamespace->addClass(static::SCHEMA_REGISTRY_CLASS);

        $types = [];

        foreach ($classes as $classDefinition) {
            $types[$classDefinition->typeName] = new Literal($this->getClassNamespaceAdd($classDefinition->className) . '\\' . $classDefinition->className . '::class');
        }

        $class->addConstant('VERSION', '1.8.36') // todo implement version detection
            ->setPublic();

        $class->addConstant('TYPES', $types)
            ->setPublic();

        $hasTypeMethod = $class->addMethod('hasType')
            ->setPublic()
            ->setStatic()
            ->setReturnType('bool');

        $hasTypeMethod->addParameter('type')
            ->setType('string');

        $hasTypeMethod->addBody('return isset(static::TYPES[$type]);');

        $getTypeClassMethod = $class->addMethod('getTypeClass')
            ->setPublic()
            ->setStatic()
            ->setReturnType('string');

        $getTypeClassMethod->addParameter('type')
            ->setType('string');

        $getTypeClassMethod->addBody('if (!static::hasType($type)) {');
        $getTypeClassMethod->addBody('    throw new InvalidArgumentException(');
        $getTypeClassMethod->addBody('        sprintf(\'Type "%s" not found in registry\', $type)');
        $getTypeClassMethod->addBody('    );');
        $getTypeClassMethod->addBody('}');
        $getTypeClassMethod->addBody('');
        $getTypeClassMethod->addBody('return static::TYPES[$type];');

        $fromArrayMethod = $class->addMethod('fromArray')
            ->setPublic()
            ->setStatic()
            ->setReturnType('TdObject');

        $fromArrayMethod->addParameter('array')
            ->setType('array');

        $fromArrayMethod->addBody('if (!isset($array[\'@type\'])) {');
        $fromArrayMethod->addBody('    throw new InvalidArgumentException(\'Can\\\'t find "@type" key in array\');');
        $fromArrayMethod->addBody('}');
        $fromArrayMethod->addBody('');
        $fromArrayMethod->addBody('$type = $array[\'@type\'];');
        $fromArrayMethod->addBody('$extra = $array[\'@extra\'] ?? null;');
        $fromArrayMethod->addBody('$typeClass = static::getTypeClass($type);');
        $fromArrayMethod->addBody('');
        $fromArrayMethod->addBody('$obj = call_user_func($typeClass . \'::fromArray\', $array);');
        $fromArrayMethod->addBody('');
        $fromArrayMethod->addBody('return $obj->setTdExtra($extra);');

        return $phpFile;
    }

    public function generateTypeSerializeInterface(): PhpFile
    {
        $phpFile = new PhpFile();
        $phpFile->addComment('This phpFile is auto-generated.');
        //        $phpFile->setStrictTypes(); // adds declare(strict_types=1)

        $phpNamespace = $phpFile->addNamespace($this->baseNamespace);

        $typeSerializerInterface = $phpNamespace->addInterface(
            static::TYPE_SERIALIZER_INTERFACE
        );

        $typeSerializerInterface->addMethod('typeSerialize')
            ->setPublic()
            ->setReturnType('array');

        return $phpFile;
    }

    private function calculateNamespace(string $className): string
    {
        $namespace = $this->getClassNamespaceAdd($className);

        return $this->baseNamespace . ($namespace ? "\\$namespace" : '');
    }

    private function getClassNamespaceAdd(string $className): ?string
    {
        preg_match('/([A-Z][a-z]+)([A-z0-9]+)?/', $className, $matches);

        return $matches[1] ?? null;
    }
}
