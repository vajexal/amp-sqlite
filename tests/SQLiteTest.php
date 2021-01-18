<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Tests;

use Amp\Sql\ConnectionException;
use Amp\Sql\QueryError;
use Vajexal\AmpSQLite\Command\Response\CommandResultResponse;
use Vajexal\AmpSQLite\SQLiteCommandResult;
use Vajexal\AmpSQLite\SQLiteConnection;
use Vajexal\AmpSQLite\SQLiteDriver;
use Vajexal\AmpSQLite\SQLiteResultSet;
use Vajexal\AmpSQLite\SQLiteStatement;
use Vajexal\AmpSQLite\Tests\Command\GetStatementsCommand;
use function Vajexal\AmpSQLite\connect;

abstract class SQLiteTest extends TestCase
{
    protected function setUpAsync()
    {
        $this->setTimeout(5000);
    }

    public function testComplex()
    {
        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->execute('create table foo (bar text not null)');
        yield $connection->execute('insert into foo (bar) values (:bar)', [
            ':bar' => 'test',
        ]);

        /** @var SQLiteResultSet $results */
        $results = yield $connection->query('select * from foo');
        yield $this->compareResultSets([['bar' => 'test']], $results);

        /** @var SQLiteStatement $statement */
        $statement = yield $connection->prepare('select * from foo where bar = :bar');
        $results   = yield $statement->execute([
            ':bar' => 'test',
        ]);
        yield $this->compareResultSets([['bar' => 'test']], $results);

        $connection->close();
    }

    public function testUpdateRowsCount()
    {
        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->execute('create table foo (bar text)');
        /** @var SQLiteCommandResult $result */
        $result = yield $connection->execute('insert into foo (bar) values (:bar)', [
            ':bar' => 'test',
        ]);
        $this->assertEquals(1, $result->getAffectedRowCount());

        /** @var SQLiteCommandResult $result */
        $result = yield $connection->execute('update foo set bar = :bar', [
            ':bar' => 'another test',
        ]);
        $this->assertEquals(1, $result->getAffectedRowCount());

        /** @var SQLiteCommandResult $result */
        $result = yield $connection->query('delete from foo');
        $this->assertEquals(1, $result->getAffectedRowCount());

        /** @var SQLiteResultSet $results */
        $results = yield $connection->query('select * from foo');
        yield $this->compareResultSets([], $results);
    }

    public function testUsingClosedConnection()
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Process unexpectedly exited');

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');
        $connection->close();
        yield $connection->query('select 1');
    }

    public function testBadQuery()
    {
        $this->expectException(QueryError::class);
        $this->expectExceptionMessage('near "foo": syntax error');

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');
        yield $connection->query('foo');
    }

    public function testWriteToReadonlyDatabase()
    {
        $this->expectException(QueryError::class);
        $this->expectExceptionMessage('attempt to write a readonly database');

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:', SQLITE3_OPEN_READONLY);

        yield $connection->execute('create table foo (bar text not null)');
    }

    public function testStatementsDestruction()
    {
        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->execute('create table foo (bar text not null)');

        /** @var SQLiteStatement $statement */
        $statement = yield $connection->prepare('select * from foo where bar = :bar');
        $results   = yield $statement->execute([
            ':bar' => 'test',
        ]);
        yield $this->compareResultSets([], $results);

        unset($statement);

        /** @var SQLiteDriver $driver */
        $driver = getPrivateProperty($connection, 'driver');

        /** @var CommandResultResponse $response */
        $response = yield $driver->send(new GetStatementsCommand);

        $this->assertEquals(0, $response->getAffectedRowCount());
    }

    public function testBlob()
    {
        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->execute('create table foo (bar blob)');

        $str = \random_bytes(2 * 1024 * 1024);

        $result = yield $connection->execute('insert into foo (bar) values (:bar)', [
            ':bar' => $str,
        ]);
        $this->assertEquals(1, $result->getAffectedRowCount());

        /** @var SQLiteResultSet $results */
        $results = yield $connection->query('select * from foo');
        yield $this->compareResultSets([['bar' => $str]], $results);
    }
}
