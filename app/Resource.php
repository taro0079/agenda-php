<?php

namespace App;


class Resource
{
	public string $path;
	public PropertyCollection $properties;
	public Method $method;

	public function __construct(){
	}

	public function setProperty(array $properties)
	{
		$result = [];
		foreach ($properties as $key => $value) {
			$property = new Property();
			$property->propertyName = $key;
			$property->setType($value['type']);
			$result[] = $property;
		}
		$this->properties = PropertyCollection::createFromArray($result);
	}

	public function setMethod(string $method): void
	{
		$this->method = Method::from($method);
	}



}
