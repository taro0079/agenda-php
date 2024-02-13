<?php

require 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

use function _\map;


class ClassCreatorCommand
{
    public static function createClassTemplate(Resource $info): string
    {
        $path = $info->path;
        $className = ResourceCommand::generateClassName($info);
        $memberProps = ResourceCommand::createMemberProps($info);
        $constructor = ResourceCommand::createConstructor($info);
        $text = "<?php\n//endpoint: %s\n//%s resource class\nclass %s\n{\n%s\n%s}";
        return sprintf($text, $path, $info->schemaType, $className, $memberProps, $constructor);
    }

    public static function createClassFile(ClassCreator $class): void
    {
        file_put_contents($class->fileName, $class->content);
    }
}


class ClassCreator
{
    public string $className;
    public string $fileName;
    public string $content;
    public function __construct(Resource $resource)
    {
        $this->className = ResourceCommand::generateClassName($resource);
        $this->content = ClassCreatorCommand::createClassTemplate($resource);
        $this->fileName = ResourceCommand::generateFileName($resource);
    }
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

class ResourceCommand
{
    public static function generateClassName(Resource $resource): string
    {
        $path = $resource->path;
        $parts = preg_split('/[\/\-]/', $path);
        $parts = array_merge($parts, [ $resource->schemaType ]);
        $parts = map($parts, fn ($part) => ucfirst($part));
        return implode('', $parts);
    }

    public static function generateFileName(Resource $resource): string
    {
        $className = self::generateClassName($resource);
        return sprintf('%s.php',$className);
    }

    public static function createMemberProps(Resource $info): string
    {
        $properties = $info->properties;
        $text = [];
        foreach ($properties as $property) {
            $row = sprintf('  public ?%s $%s = null;', $property['type'], $property['name']);
            $text[] = $row;
        };
        $result = implode("\n", $text);
        return $result;
    }
    public static function createConstructor(Resource $info): string
    {
        $text = "public function __construct(\n%s\n){\n%s\n}\n";
        $args = self::createArgForConstructor($info);
        $body = self::createConstructorBody($info);
        return sprintf($text, $args, $body);
    }

    private static function createArgForConstructor(Resource $info): string
    {
        $properties = $info->properties;
        $text = array_map(fn ($property) => sprintf('    %s $%s,', $property['type'], $property['name']), $properties);
        return implode("\n", $text);
    }

    private static function createConstructorBody(Resource $info): string
    {
        $properties = $info->properties;
        $text = array_map(fn ($property) => sprintf("    \$this->%s = $%s;", $property['name'], $property['name']), $properties);
        return implode("\n", $text);
    }
}

class Resource
{
    public function __construct(
        public ?array $properties,
        public ?string $path,
        public ?string $schemaType,
        public ?string $method,
        public ?int $statusCode = null,
    ) {
    }
}

function createPostRequest(string $endPoint, string $method, array $properties): Resource
{
    $properties = array_map(fn ($key, $props) => ["name" => $key, "type" => typeConverter($props['type'])], array_keys($properties), array_values($properties));
    return new Resource(
        schemaType: "request",
        path: $endPoint,
        method: $method,
        properties: $properties
    );
}

function createProperty(array $properties): array
{
    $func = function ($key, $prop) {
        return ["name" => $key, "type" => typeConverter($prop["type"])];
    };
    return array_map($func, array_keys($properties), array_values($properties));
}

function createPostResponse(string $statusCode, array $properties, string $endPoint): Resource
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

    $result = new Resource(
        schemaType: "response",
        method: 'post',
        path: $endPoint,
        statusCode: $statusCode,
        properties: $resultProperties
    );
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


    $responseBody = $path[$method]['responses'];
    $isStatusOk = isExsistStatusOk($responseBody);
    if ($isStatusOk === false) {
        continue;
    }
    var_dump($responseBody['200']['content']['application/json']['schema']);
    $responseProps = $responseBody['200']['content']['application/json']['schema']['properties'];
    $result = createPostResponse(statusCode: 200, properties: $responseProps, endPoint: $ep);

    if ($method !== 'post') {
        continue;
    }

    // Request
    $props = $path[$method]['requestBody']['content']['application/json']['schema']['properties'];
    $info = createPostRequest(endPoint: $ep, method: $method, properties: $props);
    $responses[] = $result;
    $infos[] = $info;
}
$all = [...$responses, ...$infos];
$f = function ($info) {
    if (null === $info) {
        return;
    }
    return new ClassCreator($info);
};
$classes = map($all, $f);
map($classes, fn(ClassCreator $class) => ClassCreatorCommand::createClassFile($class));
