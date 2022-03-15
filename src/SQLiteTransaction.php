<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

use Amp\Promise;
use Amp\Sql\ConnectionException;
use Amp\Sql\FailureException;
use Amp\Sql\QueryError;
use Amp\Sql\Transaction;
use Amp\Sql\TransactionError;
use InvalidArgumentException;
use function Amp\call;

class SQLiteTransaction implements Transaction
{
    public const ISOLATION_DEFERRED  = 0;
    public const ISOLATION_IMMEDIATE = 1;
    public const ISOLATION_EXCLUSIVE = 2;

    public const ISOLATION_MAP = [
        self::ISOLATION_DEFERRED  => 'DEFERRED',
        self::ISOLATION_IMMEDIATE => 'IMMEDIATE',
        self::ISOLATION_EXCLUSIVE => 'EXCLUSIVE',
    ];

    private ?SQLiteConnection $connection;
    private int               $isolation;

    public function __construct(SQLiteConnection $connection, int $isolation)
    {
        $this->connection = $connection;
        $this->isolation  = $isolation;
    }

    public function __destruct()
    {
        if ($this->isAlive()) {
            Promise\rethrow($this->rollback());
        }
    }

    /**
     * @inheritDoc
     * @throws TransactionError
     */
    public function query(string $sql): Promise
    {
        if (!$this->isAlive()) {
            throw new TransactionError('Transaction has been closed');
        }

        return $this->connection->query($sql);
    }

    /**
     * @inheritDoc
     * @throws TransactionError
     */
    public function prepare(string $sql): Promise
    {
        if (!$this->isAlive()) {
            throw new TransactionError('Transaction has been closed');
        }

        return $this->connection->prepare($sql);
    }

    /**
     * @inheritDoc
     * @throws TransactionError
     */
    public function execute(string $sql, array $params = []): Promise
    {
        if (!$this->isAlive()) {
            throw new TransactionError('Transaction has been closed');
        }

        return $this->connection->execute($sql, $params);
    }

    /**
     * @inheritDoc
     * @return Promise<null>
     */
    public function close(): Promise
    {
        return call(function () {
            if (!$this->isAlive()) {
                return;
            }

            yield $this->commit();
        });
    }

    /**
     * @inheritDoc
     */
    public function getIsolationLevel(): int
    {
        return $this->isolation;
    }

    /**
     * @inheritDoc
     */
    public function isActive(): bool
    {
        return $this->connection !== null;
    }

    /**
     * @inheritDoc
     * @throws TransactionError
     * @throws ConnectionException
     * @throws FailureException
     * @throws QueryError
     */
    public function commit(): Promise
    {
        if (!$this->isAlive()) {
            throw new TransactionError('Transaction has been closed');
        }

        $promise = $this->connection->execute('COMMIT');

        $this->connection = null;

        return $promise;
    }

    /**
     * @inheritDoc
     * @throws TransactionError
     * @throws ConnectionException
     * @throws FailureException
     * @throws QueryError
     */
    public function rollback(): Promise
    {
        if (!$this->isAlive()) {
            throw new TransactionError('Transaction has been closed');
        }

        $promise = $this->connection->execute('ROLLBACK');

        $this->connection = null;

        return $promise;
    }

    /**
     * @inheritDoc
     * @throws TransactionError
     * @throws ConnectionException
     * @throws FailureException
     * @throws QueryError
     */
    public function createSavepoint(string $identifier): Promise
    {
        if (!$this->isAlive()) {
            throw new TransactionError('Transaction has been closed');
        }

        $this->validateSavepointIdentifier($identifier);

        return $this->connection->execute("SAVEPOINT {$identifier}");
    }

    /**
     * @inheritDoc
     * @throws TransactionError
     * @throws ConnectionException
     * @throws FailureException
     * @throws QueryError
     */
    public function rollbackTo(string $identifier): Promise
    {
        if (!$this->isAlive()) {
            throw new TransactionError('Transaction has been closed');
        }

        $this->validateSavepointIdentifier($identifier);

        return $this->connection->execute("ROLLBACK TO {$identifier}");
    }

    /**
     * @inheritDoc
     * @throws TransactionError
     * @throws ConnectionException
     * @throws FailureException
     * @throws QueryError
     */
    public function releaseSavepoint(string $identifier): Promise
    {
        if (!$this->isAlive()) {
            throw new TransactionError('Transaction has been closed');
        }

        $this->validateSavepointIdentifier($identifier);

        return $this->connection->execute("RELEASE {$identifier}");
    }

    private function validateSavepointIdentifier(string $identifier)
    {
        if (!\preg_match('/^[a-zA-Z_]\w*$/', $identifier)) {
            throw new InvalidArgumentException("Invalid savepoint identifier {$identifier}");
        }
    }

    /**
     * @inheritDoc
     */
    public function isAlive(): bool
    {
        return $this->connection && $this->connection->isAlive();
    }

    /**
     * @inheritDoc
     */
    public function getLastUsedAt(): int
    {
        // I don't think we need last used timestamp when transaction is closed
        return $this->connection ? $this->connection->getLastUsedAt() : 0;
    }
}
