<?php

namespace Robertbaelde\LaravelDynamodb;

use Aws\DynamoDb\DynamoDbClient;

class Migrator
{
    public function __construct(
        protected DynamoDbClient $client,
        protected string $prefix = '',
    )
    {

    }

    public function run(DynamoMigration ...$dynamoMigrations)
    {
        foreach ($dynamoMigrations as $migration){
            $tableName = $this->prefix . $migration->getTableName();
            $this->client->createTable(
                array_merge(
                    [
                        'TableName' => $tableName,
                    ],
                    $this->getKeySchema($migration),
                    $this->getBillingSettings($migration),
                    $this->getGSI($migration),
                    $this->getAttributeDefinitions($migration),
                )
            );
        }
    }

    private function getKeySchema(DynamoMigration $migration): array
    {
        $keySchema = [];

        $keySchema[] = [
            'AttributeName' => $migration->getPartitionKey()->attributeName,
            'KeyType' => KeyType::PARTITION->value,
        ];

        if ($migration->getSortKey() instanceof Key) {
            $keySchema[] = [
                'AttributeName' => $migration->getSortKey()->attributeName,
                'KeyType' => KeyType::SORT->value
            ];
        }
        return ['KeySchema' => $keySchema];
    }

    private function getGSI(DynamoMigration $migration): array
    {
        $globalSecondaryIndexes = [];
        /** @var GlobalSecondaryIndex $globalSecondaryIndex */
        foreach ($migration->getGlobalSecondaryIndexes() as $globalSecondaryIndex){
            $globalSecondaryIndexes[] = [
                'IndexName' => $globalSecondaryIndex->name,
                'KeySchema' => [
                    [
                        'AttributeName' => $globalSecondaryIndex->partitionKey->attributeName,
                        'KeyType' => KeyType::PARTITION->value
                    ],
                    [
                        'AttributeName' => $globalSecondaryIndex->sortKey->attributeName,
                        'KeyType' => KeyType::SORT->value
                    ]
                ],
                'Projection' => $globalSecondaryIndex->projection->toApi()
            ];
        }
        if(count($globalSecondaryIndexes) === 0){
            return [];
        }
        return ['GlobalSecondaryIndexes' => $globalSecondaryIndexes];
    }

    private function getBillingSettings(DynamoMigration $migration): array
    {
        return $migration->getProvisionedThroughput() !== null ? [
            'BillingMode' => $migration->getBillingMode()->value,
            'ProvisionedThroughput' => $migration->getProvisionedThroughput()->toApi()
        ] : [
            'BillingMode' => $migration->getBillingMode()->value,
        ];
    }

    private function getAttributeDefinitions(DynamoMigration $migration): array
    {
        $attributeDefinitions = [[
            'AttributeName' => $migration->getPartitionKey()->attributeName,
            'AttributeType' => $migration->getPartitionKey()->attributeType->value,
        ]];

        if ($migration->getSortKey() instanceof Key) {
            $attributeDefinitions[] = [
                'AttributeName' => $migration->getSortKey()->attributeName,
                'AttributeType' => $migration->getSortKey()->attributeType->value,
            ];
        }

        /** @var GlobalSecondaryIndex $globalSecondaryIndex */
        foreach ($migration->getGlobalSecondaryIndexes() as $globalSecondaryIndex) {
            $attributeDefinitions[] = $globalSecondaryIndex->partitionKey->toAttributeDefinition();
            $attributeDefinitions[] = $globalSecondaryIndex->sortKey->toAttributeDefinition();
        }

        return ['AttributeDefinitions' => $attributeDefinitions];
    }


}
