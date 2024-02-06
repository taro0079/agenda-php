<?php

use App\Property;
use App\PropertyCollection;
use App\Type;
use PHPUnit\Framework\TestCase;

class PropertyCollectionTest extends TestCase
{

    public function testCreateFromArray(): void
    {
        $array = [
            "properties" => [
                "orderId" => [
                    "type" => "integer",
                ],
                "customerId" => [
                    "type" => "integer"
                ]
            ]
        ];

        $properties = PropertyCollection::createFromArray($array);
        $this->assertInstanceOf(PropertyCollection::class, $properties);
        $this->assertInstanceOf(Property::class, $properties->properties[0]);
        $this->assertSame('orderId', $properties->properties[0]->propertyName);
        $this->assertSame(Type::IN, $properties->properties[0]->type);
    }
}
