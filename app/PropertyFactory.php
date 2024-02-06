<?php

use App\Method;

class PropertyFactory
{
    private Method $method;

    private function __construct(Method $method): void
    {
        $this->method = $method;
    }


    public function create(Method $method): array
    {
    }

    private function createPropery(array $apiInfoSchema)
    {
        $properties = [];
        if (Method::POST) {
            $requestBody = $apiInfoSchema['requestBody'];

            foreach ($requestBody['content'] as $key => $value) {
                foreach ($value as $key => $value) {
                    $properties[] = $value['properties'];
                }
            }
        } elseif (Method::GET) {
        }
    }
}
