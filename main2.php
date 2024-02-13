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
	$schemaType = $info['schemaType'];
	$className = sprintf("%s_%s", $path, $schemaType);
	$memberProps = createMemberProps($info);
	$constructor = createConstructor($info);
	$text = "<?php\nclass %s\n{\n%s\n%s}";
	return sprintf($text, $className, $memberProps, $constructor);
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

class Resource
{
	public function __construct(
		public ?array $properties,
		public ?string $schemaType,
		public ?string $method,
	) {
	}
}

function createPostRequest(string $endPoint, string $method, array $properties): array
{
	$properties = array_map(fn ($key, $props) => ["name" => $key, "type" => typeConverter($props['type'])], array_keys($properties), array_values($properties));
	return [
		"schemaType" => "request",
		"path" => $endPoint,
		"mehtod" => $method,
		"properties" => $properties
	];
}

function createProperty(array $properties): array
{
	$func = function ($key, $prop) {
		return ["name" => $key, "type" => typeConverter($prop["type"])];
	};
	return array_map($func, array_keys($properties), array_values($properties));
}

function createPostResponse(string $statusCode, array $properties, string $endPoint): array
{
	$resultProperties = [];
	foreach ($properties as $key => $props) {
		if ($key === "data") {
			$properties = $props['items']['properties'];
			$additionalProperties = createProperty($properties);
			foreach ($additionalProperties as $prop) {
				$resultProperties[] = $prop;
			}
			continue;
		}
		if ($key === "pagination") {
			continue;
		}
		$resultProperties[] = ["name" => $key, "type" => typeConverter($props['type'])];
	}

	$result = [
		"schemaType" => "response",
		"path" => $endPoint,
		"statusCode" => $statusCode,
		"properties" => $resultProperties,
	];
	return $result;
}

function extractProperties(array $requestBody, string $endPoint): array
{
	$response = [];
	foreach ($requestBody as $key => $value) {
		$statusCode = $key;

		if ($statusCode !== 200) {
			continue;
		}

		$properties = $value['content']['application/json']['schema']['properties'];
		$result = createPostResponse(statusCode: $statusCode, properties: $properties, endPoint: $endPoint);
		$response[] = $result;
	}
	return $response;
}

function isExsistStatusOk(array $responseBody): bool
{
	foreach ($responseBody as $key => $value) {
		$statusCode = $key;
		if ($statusCode === 200) {
			return true;
		}
	}
	return false;
}

$data = Yaml::parseFile("./order.yaml");

$paths = null;
foreach ($data as $key => $value) {
	if ($key === 'paths') {
		$paths = $value;
	}
}
$infos = [];
$responses = [];
foreach ($paths as $key => $path) {
	$ep = $key;
	$method = key($path);

	if ($method !== 'post') {
		continue;
	}

	// Request
	$props = $path[$method]['requestBody']['content']['application/json']['schema']['properties'];
	$info = createPostRequest(endPoint: $ep, method: $method, properties: $props);

	$responseBody = $path[$method]['responses'];
	$isStatusOk = isExsistStatusOk($responseBody);
	if ($isStatusOk === false) {
		continue;
	}
	$responseProps = $responseBody['200']['content']['application/json']['schema']['properties'];
	$result = createPostResponse(statusCode: 200, properties: $responseProps, endPoint: $ep);
	$responses[] = $result;
	$infos[] = $info;
}
$all = [...$responses, ...$infos];
$f = function ($info) {
	if (null === $info) {
		return;
	}
	return createClassTemplate($info);
};
$classes = array_map($f, $all);
var_dump($classes);