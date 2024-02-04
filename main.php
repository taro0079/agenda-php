<?php

require 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;
enum Type: string
{
	case IN = 'integer';
	case ST = 'string';

	public function phpType()
	{
		return match($this) {
			self::IN => 'int',
			self::ST => 'string',
		};
	}
}


enum Method: string
{
	case GET = 'get';
	case POST = 'post';
	case PUT = 'put';
}

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

	public function createValuesText():string
	{
		$result = '';
		foreach($this->properties->properties as $property)
		{
			$text = $property->createMemberValText();
			$result = sprintf('%s%s%s%s', $result, $text,"\n", "\n");
		}
		return $result;
	}

	public function writeResourceFile(string $fileName)
	{
		$content =  <<< EOD
			#[ApiResource(
				operations: []
				)	
			]
			class Test
			{
				%s
				public function __construct(){
				}
			}
		EOD;
		$content = sprintf($content, $this->createValuesText());
		file_put_contents($fileName, $content);
		
	}

}

class ResourceMaker
{
	public function __construct(Resource $resource)
	{
	}
}



$data = Yaml::parseFile("./test.yaml");

$paths = [];
foreach ($data as $key => $value) {
	if ('paths' === $key) {
		$paths[] = $value;
	}
}

$pathInfo= [];
$resources = [];
foreach ($paths as $value) {
	$resource = new Resource();
	$path = key($value);
	$resource->path = $path;
	$info = $value[$path];
	foreach ($info as $key => $value) {
		$method = $key;
		$resource->setMethod($method);
		$requestBody = $value['requestBody'];
		foreach ($requestBody['content'] as $key => $value) {
			foreach ($value as $key => $value) {
				$resource->setProperty($value['properties']);
			}
			
		}
	}
	$resources[] = $resource;
}
//var_dump($resources[0]->createValuesText());
$resources[0]->writeResourceFile('test.txt');


