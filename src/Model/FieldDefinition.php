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
}
