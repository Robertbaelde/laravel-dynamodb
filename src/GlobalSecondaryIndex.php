<?php

namespace Robertbaelde\LaravelDynamodb;

class GlobalSecondaryIndex
{

    public function __construct(
        public readonly string $name,
        public readonly Key $partitionKey,
        public readonly ?Key $sortKey,
        public readonly Projection $projection,
        public readonly ?ProvisionedThroughput $provisionedThroughput = null,
    )
    {
    }
}
