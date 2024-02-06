<?php

namespace App;

class Property
{
    public string $propertyName;
    public Type $type;

    private function __construct(
        string $propertyName,
        Type $type
    ) {
        $this->propertyName = $propertyName;
        $this->type = $type;
    }



    public function setType(string $type)
    {
        $this->type = Type::from($type);
    }

    public function createMemberValText(): string
    {
        $type = $this->type->phpType();
        $text = sprintf('public ?%s $%s = null;', $type, $this->propertyName);
        return $text;
    }

    public static function create(
        string $propertyName,
        Type $type
    ) {
        return new self(
            propertyName: $propertyName,
            type: $type
        );
    }
}
