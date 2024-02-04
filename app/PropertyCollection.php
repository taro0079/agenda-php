<?php

namespace App;

class PropertyCollection
{
	public array $properties;


	public static function createFromArray(array $properties){
		return new self(
			properties: $properties
		);
	}

	public function __construct(array $properties){
		$this->properties = $properties;
	}

}
