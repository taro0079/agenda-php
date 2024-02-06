<?php

namespace App;

enum Type: string
{
    case IN = 'integer';
    case ST = 'string';
    case OB = 'object';

    public function phpType()
    {
        return match ($this) {
            self::IN => 'int',
            self::ST => 'string',
            self::OB => 'object',
        };
    }
    public static function createFromArray(array $infoArray): static
    {
        return self::from($infoArray['type']);
    }
}
