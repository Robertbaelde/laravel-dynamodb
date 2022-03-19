<?php

namespace Robertbaelde\LaravelDynamodb\Test;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function deleteTable(string $tableName)
    {
        try {
            $this->getClient()->deleteTable(['TableName' => $tableName]);
        } catch (DynamoDbException $exception){
            if($exception->getAwsErrorCode() !== 'ResourceNotFoundException'){
                throw $exception;
            }
        }
    }

    protected function getClient(): DynamoDbClient
    {
        $sdk = new \Aws\Sdk([
            'endpoint'   => 'http://localhost:8000',
            'region'   => 'us-east-1',
            'version'  => 'latest',
            'credentials' => [
                'key' => 'S3_KEY',
                'secret' => 'S3_SECRET'
            ]
        ]);

        return $sdk->createDynamoDb();
    }
}
