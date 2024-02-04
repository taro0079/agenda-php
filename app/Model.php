<?php

namespace App;

class Model
{
	public string $test;

	public function __construct(string $test)
	{
		$this->test = $test;
	}
}
