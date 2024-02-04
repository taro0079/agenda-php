<?php

use App\Resource;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
	public function testResource():void
	{
		$resource = new Resource();
		$this->assertInstanceOf(Resource::class, $resource);
	}

}
