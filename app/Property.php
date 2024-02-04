<?php

namespace App;

class Property
{
	public string $propertyName;
	public Type $type;
	public function __construct(){
	}

	public function setType(string $type) {
		$this->type = Type::from($type);
	}

	public function createMemberValText(): string
	{
		$type = $this->type->phpType();
		$text = sprintf('public ?%s $%s = null;', $type, $this->propertyName); 
		return $text;
	}

}
