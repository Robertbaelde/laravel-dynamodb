<?php

namespace Robertbaelde\LaravelDynamodb;

abstract class DynamoMigration
{
    public function __construct()
    {
        $this->validate();
    }

    abstract public function getTableName(): string;

    abstract public function getPartitionKey(): Key;

    abstract public function getBillingMode(): BillingMode;

    public function getSortKey(): ?Key
    {
        return null;
    }

    public function getProvisionedThroughput(): ?ProvisionedThroughput
    {
        return $this->getBillingMode() === BillingMode::PayPerRequest ?
            null :
            new ProvisionedThroughput(3, 3);
    }

    public function getGlobalSecondaryIndexes(): iterable
    {
        return [];
    }

    private function validate(): void
    {
        foreach ($this->getGlobalSecondaryIndexes() as $gsi) {
            assert($gsi instanceof GlobalSecondaryIndex);
        }
    }
}
