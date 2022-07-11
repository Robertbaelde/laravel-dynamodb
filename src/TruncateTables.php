<?php

namespace Robertbaelde\LaravelDynamodb;

use Aws\DynamoDb\DynamoDbClient;

class TruncateTables
{
    public function __construct(
        protected DynamoDbClient $client,
        protected string $prefix = '',
    ) {
    }

    public function run(DynamoMigration ...$dynamoMigrations)
    {
        foreach ($dynamoMigrations as $migration) {
            $tableName = $this->prefix . $migration->getTableName();

            $scanResult = $this->client->scan([
                'TableName' => $tableName,
            ]);

            foreach($scanResult->toArray()['Items'] as $item){
                $key = [
                    $item[$migration->getPartitionKey()->attributeName] => $item[$migration->getPartitionKey()->attributeName],
                ];

                if(array_key_exists($migration->getSortKey()->attributeName, $item)){
                    $key[$migration->getSortKey()->attributeName] = $item[$migration->getSortKey()->attributeName];
                }

                $this->client->deleteItem([
                    'TableName' => $tableName,
                    'Key' => $key
                ]);
            }
        }
    }
}
