<?php
namespace App;

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
