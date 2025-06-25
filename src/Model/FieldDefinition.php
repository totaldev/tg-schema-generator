<?php

declare(strict_types=1);

namespace totaldev\SchemaGenerator\Model;

/**
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class FieldDefinition
{
    public string $doc;

    public bool $mayBeNull;

    public string $name;

    public string $rawName;

    public string $type;

    private mixed $defaultValue;

    public function getDefaultValue(): mixed
    {
        if ($this->defaultValue === 'null') {
            return null;
        }

        return $this->defaultValue;
    }

    public function setDefaultValue(mixed $val): static
    {
        if ($val === null) {
            $this->defaultValue = 'null';

            return $this;
        }
        $this->defaultValue = $val;

        return $this;
    }

    public function hasDefaultValue(): bool
    {
        return isset($this->defaultValue) && $this->defaultValue !== null;
    }
}
