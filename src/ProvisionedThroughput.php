<?php

namespace Robertbaelde\LaravelDynamodb;

class ProvisionedThroughput
{
    public function __construct(
        public readonly int $readCapacityUnits,
        public readonly int $writeCapacityUnits
    )
    {

    }

    public function toApi(): array
    {
        return [
            'ReadCapacityUnits' => $this->readCapacityUnits,
            'WriteCapacityUnits' => $this->writeCapacityUnits
        ];
    }
}
