<?php

namespace Robertbaelde\LaravelDynamodb;

use Aws\DynamoDb\DynamoDbClient;

class DeleteTables
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
            $this->client->deleteTable(
                [
                    'TableName' => $tableName,
                ]
            );
        }
    }
}
