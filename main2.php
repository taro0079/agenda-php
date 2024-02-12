<?php

require 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

function createMemberProps(array $info): string
{
	$properties = $info['properties'];
	$text = [];
	foreach ($properties as $property) {
		$row = sprintf('  public ?%s $%s = null;', $property['type'], $property['name']);
		$text[] = $row;
	};
	$result = implode("\n", $text);
	return $result;
};

function createClassTemplate(array $info): string
{
	$path = $info['path'];
	$memberProps = createMemberProps($info);
	$constructor = createConstructor($info);
	$text = "<?php\nclass %s\n{\n%s\n%s}";
	return sprintf($text, $path, $memberProps, $constructor);
};

function createArgForConstructor(array $info): string
{
	$properties = $info['properties'];
	$text = array_map(fn ($property) => sprintf('    %s $%s,', $property['type'], $property['name']), $properties);
	return implode("\n", $text);
}
function createConstructorBody(array $info): string
{
	$properties = $info['properties'];
	$text = array_map(fn ($property) => sprintf("    \$this->%s = $%s;", $property['name'], $property['name']), $properties);
	return implode("\n", $text);
}
function createConstructor(array $info): string
{
	$text = "public function __constructor(\n%s\n){\n%s\n}\n";
	$args = createArgForConstructor($info);
	$body = createConstructorBody($info);
	return sprintf($text, $args, $body);
}

function typeConverter(string $type): string
{
	return match ($type) {
		"integer" => "int",
		"object" => "array",
		"boolean" => "bool",
		"array" => "array",
		"string" => "string",
	};
}

$data = Yaml::parseFile("./order.yaml");

$paths = null;
foreach ($data as $key => $value) {
	if ($key === 'paths') {
		$paths = $value;
	}
}
$infos = [];
foreach ($paths as $key => $path) {
	$ep = $key;
	$method = key($path);
	$props = $path[$method]['requestBody']['content']['application/json']['schema']['properties'];
	$properties = [];
	foreach ($props as $name => $prop) {
		$type = typeConverter($prop['type']);
		$properties[] = ["name" => $name, "type" => $type];
	}
	$info = [
		"path" => $ep,
		"mehtod" => $method,
		"properties" => $properties
	];
	$infos[] = $info;
}
$f = fn ($info) => createClassTemplate($info);

$classes = array_map($f, $infos);
var_dump($classes);
