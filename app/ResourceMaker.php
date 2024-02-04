<?php

namespace App;

use App\Resource;

class ResourceMaker
{
	public Resource $resource;
	public function __construct(Resource $resource)
	{
		$this->resource = $resource;
	}

	public function createConstruct(): string
	{

	}

	public function createValuesText():string
	{
		$result = '';
		foreach($this->resource->properties->properties as $property)
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
