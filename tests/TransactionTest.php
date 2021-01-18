<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Tests;

use Amp\Loop;
use Amp\Sql\ConnectionException;
use Amp\Sql\QueryError;
use Amp\Sql\Transaction;
use Amp\Sql\TransactionError;
use InvalidArgumentException;
use LogicException;
use Vajexal\AmpSQLite\SQLiteConnection;
use Vajexal\AmpSQLite\SQLiteResultSet;
use Vajexal\AmpSQLite\SQLiteTransaction;
use function Vajexal\AmpSQLite\connect;

class TransactionTest extends TestCase
{
    protected function setUpAsync()
    {
        $this->setTimeout(5000);
    }

    public function testTransaction()
    {
        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->execute('create table foo (bar text not null)');

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();
        $this->assertEquals(SQLiteTransaction::ISOLATION_DEFERRED, $transaction->getIsolationLevel());
        $this->assertTrue($transaction->isActive());
        $this->assertTrue($transaction->isAlive());
        yield $transaction->execute('insert into foo (bar) values (:bar)', [
            ':bar' => 'test',
        ]);

        yield $transaction->createSavepoint('test');
        yield $transaction->execute('update foo set bar = :bar', [
            ':bar' => 'test2',
        ]);
        yield $transaction->releaseSavepoint('test');

        yield $transaction->execute('update foo set bar = :bar', [
            ':bar' => 'test3',
        ]);
        yield $transaction->commit();
        $this->assertFalse($transaction->isActive());
        $this->assertFalse($transaction->isAlive());

        /** @var SQLiteResultSet $results */
        $results = yield $connection->query('select * from foo');
        yield $this->compareResultSets([['bar' => 'test3']], $results);
    }

    public function testRollback()
    {
        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->execute('create table foo (bar text not null)');
        yield $connection->execute('insert into foo (bar) values (:bar)', [
            ':bar' => 'test',
        ]);

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();
        yield $transaction->createSavepoint('test');
        yield $transaction->execute('update foo set bar = :bar', [
            ':bar' => 'test2',
        ]);
        yield $transaction->rollback();
        $transaction->close(); // Shouldn't throw error

        /** @var SQLiteResultSet $results */
        $results = yield $connection->query('select * from foo');
        yield $this->compareResultSets([['bar' => 'test']], $results);
    }

    public function testRollbackSavepoint()
    {
        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->execute('create table foo (bar text not null)');
        yield $connection->execute('insert into foo (bar) values (:bar)', [
            ':bar' => 'test',
        ]);

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();
        yield $transaction->createSavepoint('test');
        yield $transaction->execute('update foo set bar = :bar', [
            ':bar' => 'test2',
        ]);
        yield $transaction->rollbackTo('test');
        yield $transaction->commit();

        /** @var SQLiteResultSet $results */
        $results = yield $connection->query('select * from foo');
        yield $this->compareResultSets([['bar' => 'test']], $results);
    }

    public function testClosingTransaction()
    {
        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->execute('create table foo (bar text not null)');
        yield $connection->execute('insert into foo (bar) values (:bar)', [
            ':bar' => 'test',
        ]);

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();
        yield $transaction->execute('update foo set bar = :bar', [
            ':bar' => 'test2',
        ]);
        $transaction->close();

        Loop::defer(function () use ($connection) {
            /** @var SQLiteResultSet $results */
            $results = yield $connection->query('select * from foo');
            yield $this->compareResultSets([['bar' => 'test2']], $results);
        });
    }

    public function testDestructingTransaction()
    {
        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->execute('create table foo (bar text not null)');
        yield $connection->execute('insert into foo (bar) values (:bar)', [
            ':bar' => 'test',
        ]);

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();
        yield $transaction->execute('update foo set bar = :bar', [
            ':bar' => 'test2',
        ]);
        unset($transaction);

        Loop::defer(function () use ($connection) {
            /** @var SQLiteResultSet $results */
            $results = yield $connection->query('select * from foo');
            yield $this->compareResultSets([['bar' => 'test']], $results);
        });
    }

    public function testUsingClosedTransaction()
    {
        $this->expectException(TransactionError::class);
        $this->expectExceptionMessage('Transaction has been closed');

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();
        yield $transaction->commit();

        yield $transaction->query('select 1');
    }

    public function testUsingTransactionWithClosedConnection()
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessage('Process unexpectedly exited');

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();

        $connection->close();

        yield $transaction->commit();
    }

    public function testOpenFewTransactions()
    {
        $this->expectException(QueryError::class);
        $this->expectExceptionMessage('cannot start a transaction within a transaction');

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->beginTransaction();
        yield $connection->beginTransaction();
    }

    public function testRollbackToWithoutSavepoint()
    {
        $this->expectException(QueryError::class);
        $this->expectExceptionMessage('no such savepoint: test');

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();
        yield $transaction->rollbackTo('test');
    }

    public function testReleaseWithoutSavepoint()
    {
        $this->expectException(QueryError::class);
        $this->expectExceptionMessage('no such savepoint: test');

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();
        yield $transaction->releaseSavepoint('test');
    }

    public function testIsolationLevel()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid isolation level ' . Transaction::ISOLATION_SERIALIZABLE);

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        yield $connection->beginTransaction(Transaction::ISOLATION_SERIALIZABLE);
    }

    public function testSavepointIdentifierValidation()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid savepoint identifier');

        /** @var SQLiteConnection $connection */
        $connection = yield connect(':memory:');

        /** @var SQLiteTransaction $transaction */
        $transaction = yield $connection->beginTransaction();
        yield $transaction->createSavepoint('a; delete from users');
    }
}
