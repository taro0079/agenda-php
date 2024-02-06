<?php

use App\ContentType;
use App\Schema;
use PHPUnit\Framework\TestCase;

class ContentTypeTest extends TestCase
{




    public  function testCreateFromArray(): void
    {
        $array = [
            "application/json" => [
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
            ]
        ];

        $contentType = ContentType::createFromArray($array);
        $this->assertInstanceOf(ContentType::class, $contentType);
        $this->assertInstanceOf(Schema::class, $contentType->schema);
    }
}
