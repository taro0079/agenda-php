<?php

use App\PropertyCollection;
use App\Schema;
use App\Type;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{

    public function testCreateFromArray(): void
    {
        $array = [
            "schema" => [
                "type" => "object",
                "required" => [
                    "orderId",
                    "orderName"
                ],
                "properties" => [
                    "orderId" => [
                        "type" => "string",
                        "description" => "order id",
                        "example" => "1",
                    ],
                    "orderName" => [
                        "type" => "string",
                        "description" => "order name",
                        "example" => "test",
                    ]
                ]
            ]
        ];

        $schema = Schema::createFromArray($array);
        $this->assertInstanceOf(Schema::class, $schema);
        $this->assertInstanceOf(Type::class , $schema->type);
        $this->assertInstanceOf(PropertyCollection::class , $schema->properties);
    }
}
