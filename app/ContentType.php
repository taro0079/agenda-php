<?php
namespace App;

use App\Schema;

class ContentType
{
    public readonly Schema $schema;


    public function __construct(
        Schema $schema
    )
    {
        $this->schema = $schema;
    }

    public static function createFromArray(array $infoArray): static
    {
        $contentType = $infoArray['application/json'];
        $schema = Schema::createFromArray($contentType);
        return new self(
            schema: $schema
        );
    }
}
