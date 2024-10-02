<?php

declare(strict_types=1);

namespace totaldev\SchemaGenerator\Model;

/**
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class ClassDefinition
{
    public string $classDocs;
    public string $className;

    /** @var FieldDefinition[] */
    public array $fields = [];
    public string $parentClass;
    public string $returnType;
    public string $typeName;

    public function getField(string $name): FieldDefinition
    {
        if (false === isset($this->fields[$name])) {
            $this->fields[$name] = new FieldDefinition();
        }

        return $this->fields[$name];
    }
}
