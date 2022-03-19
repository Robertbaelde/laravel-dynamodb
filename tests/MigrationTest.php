<?php

namespace Robertbaelde\LaravelDynamodb\Test;

use Robertbaelde\LaravelDynamodb\AttributeType;
use Robertbaelde\LaravelDynamodb\BillingMode;
use Robertbaelde\LaravelDynamodb\DynamoMigration;
use Robertbaelde\LaravelDynamodb\GlobalSecondaryIndex;
use Robertbaelde\LaravelDynamodb\Key;
use Robertbaelde\LaravelDynamodb\Migrator;
use Robertbaelde\LaravelDynamodb\Projection;
use Robertbaelde\LaravelDynamodb\ProvisionedThroughput;

class MigrationTest extends TestCase
{
    /** @test */
    public function it_can_create_a_simple_table()
    {
        $client = $this->getClient();

        $this->deleteTable((new SimpleTable())->getTableName());

        $migrator = new Migrator($client);
        $migrator->run(
            new SimpleTable(),
        );

        $response = $client->describeTable(['TableName' => (new SimpleTable())->getTableName()]);

        $this->assertEquals([
            [
                'AttributeName' => 'PK',
                'AttributeType' => 'S'
            ],
            [
                'AttributeName' => 'SK',
                'AttributeType' => 'S'
            ]
        ], $response['Table']['AttributeDefinitions']);

        $this->assertEquals([
            [
                'AttributeName' => 'PK',
                'KeyType' => 'HASH'
            ],
            [
                'AttributeName' => 'SK',
                'KeyType' => 'RANGE'
            ]
        ], $response['Table']['KeySchema']);

        $this->assertEquals('foo', $response['Table']['TableName']);
        $this->assertEquals('PAY_PER_REQUEST', $response['Table']['BillingModeSummary']['BillingMode']);
    }

    /** @test */
    public function it_can_migrate_a_simple_table_with_provisioned_mode_of_n_units()
    {
        $client = $this->getClient();

        $this->deleteTable((new TableWithDifferentBillingMode())->getTableName());

        $migrator = new Migrator($client);
        $migrator->run(
            new TableWithDifferentBillingMode(),
        );

        $response = $client->describeTable(['TableName' => (new TableWithDifferentBillingMode())->getTableName()]);
        $this->assertEquals(5, $response['Table']['ProvisionedThroughput']['ReadCapacityUnits']);
        $this->assertEquals(4, $response['Table']['ProvisionedThroughput']['WriteCapacityUnits']);
    }

    /** @test */
    public function it_prepends_configured_table_prefix()
    {
        $client = $this->getClient();

        $this->deleteTable('foo_prefix_' . (new SimpleTable())->getTableName());

        $migrator = new Migrator($client, 'foo_prefix_');
        $migrator->run(
            new SimpleTable(),
        );

        $response = $client->describeTable(['TableName' => 'foo_prefix_' . (new SimpleTable())->getTableName()]);
        $this->assertEquals('foo_prefix_foo', $response['Table']['TableName']);
    }

    /** @test */
    public function it_configures__gsi()
    {
        $client = $this->getClient();

        $this->deleteTable((new TableWithGSI())->getTableName());

        $migrator = new Migrator($client);
        $migrator->run(
            new TableWithGSI(),
        );

        $response = $client->describeTable(['TableName' => (new TableWithGSI())->getTableName()]);
        $gsis = $response['Table']['GlobalSecondaryIndexes'];
        $this->assertCount(1, $gsis);
        $gsi = $gsis[0];
        $this->assertEquals('gsi1', $gsi['IndexName']);
    }

}

class SimpleTable extends DynamoMigration
{
    public function getTableName(): string
    {
        return 'foo';
    }

    public function getPartitionKey(): Key
    {
        return new Key('PK', AttributeType::String);
    }

    public function getSortKey(): Key
    {
        return new Key('SK', AttributeType::String);
    }

    public function getBillingMode(): BillingMode
    {
        return BillingMode::PayPerRequest;
    }
}

class TableWithDifferentBillingMode extends SimpleTable
{
    public function getBillingMode(): BillingMode
    {
        return BillingMode::Provisioned;
    }

    public function getProvisionedThroughput(): ProvisionedThroughput
    {
        return new ProvisionedThroughput(5, 4);
    }
}

class TableWithGSI extends SimpleTable
{
    public function getGlobalSecondaryIndexes(): iterable
    {
       yield new GlobalSecondaryIndex(
           'gsi1',
           Key::string('GSI1-PK'),
           Key::string('GSI1-SK'),
           Projection::all()
       );
    }
}
