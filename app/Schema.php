<?php

namespace App;

use App\PropertyCollection;
use App\Type;


class Schema
{
    public readonly Type $type;
    public readonly PropertyCollection $properties;

    public function __construct(
        Type $schemaType,
        PropertyCollection $properties,
    ) {
        $this->type = $schemaType;
        $this->properties = $properties;
    }

    public static function createFromArray(array $infoArray): static
    {
        $schema = $infoArray['schema'];
        $schemaType = Type::createFromArray($schema);
        $propertyCollection = PropertyCollection::createFromArray($schema);

        return new self(
            schemaType: $schemaType,
            properties: $propertyCollection
        );
    }
}
