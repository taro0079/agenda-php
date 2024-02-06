<?php

use App\Property;
use App\Type;
use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{
    public function testCreate(): void
    {
        $propertyName = 'orderId';
        $type = Type::IN;
        $property = Property::create($propertyName,$type);

        $this->assertInstanceOf(Property::class, $property);
    }
}
