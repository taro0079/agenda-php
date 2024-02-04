<?php

require 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;
use App\Resource;
use App\ResourceMaker;

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
$maker = new ResourceMaker($resources[0]);
var_dump($maker->createValForConstruct());
//$resources[0]->writeResourceFile('test.txt');



