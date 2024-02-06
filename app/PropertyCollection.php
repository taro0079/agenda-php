<?php

namespace App;

class PropertyCollection
{
    public array $properties;


    public static function createFromArray(array $properties): static
    {
        $ps = [];
        $props = $properties['properties'];
        foreach ($props as $key => $value) {
            $propertyName = $key;
            $type = Type::from($value['type']);
            $property = Property::create(
                propertyName: $propertyName,
                type: $type
            );
            $ps[] = $property;
        }
        return new self(
            properties: $ps
        );
    }

    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }
}
