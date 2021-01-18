<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

use Amp\Promise;
use Amp\Sql\Transaction;
use Amp\Sql\TransactionError;
use InvalidArgumentException;
use Throwable;

class SQLiteTransaction implements Transaction
{
    const ISOLATION_DEFERRED  = 0;
    const ISOLATION_IMMEDIATE = 1;
    const ISOLATION_EXCLUSIVE = 2;

    const ISOLATION_MAP = [
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
     * @throws Throwable
     */
    public function query(string $sql): Promise
    {
        if (!$this->connection) {
            throw new TransactionError('Transaction has been closed');
        }

        return $this->connection->query($sql);
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function prepare(string $sql): Promise
    {
        if (!$this->connection) {
            throw new TransactionError('Transaction has been closed');
        }

        return $this->connection->prepare($sql);
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function execute(string $sql, array $params = []): Promise
    {
        if (!$this->connection) {
            throw new TransactionError('Transaction has been closed');
        }

        return $this->connection->execute($sql, $params);
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        if ($this->connection) {
            Promise\rethrow($this->commit());
        }
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
     * @throws Throwable
     */
    public function commit(): Promise
    {
        if (!$this->connection) {
            throw new TransactionError('Transaction has been closed');
        }

        $promise          = $this->connection->execute('COMMIT');
        $this->connection = null;
        return $promise;
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function rollback(): Promise
    {
        if (!$this->connection) {
            throw new TransactionError('Transaction has been closed');
        }

        $promise          = $this->connection->execute('ROLLBACK');
        $this->connection = null;
        return $promise;
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function createSavepoint(string $identifier): Promise
    {
        if (!$this->connection) {
            throw new TransactionError('Transaction has been closed');
        }

        $this->validateSavepointIdentifier($identifier);

        return $this->connection->execute("SAVEPOINT {$identifier}");
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function rollbackTo(string $identifier): Promise
    {
        if (!$this->connection) {
            throw new TransactionError('Transaction has been closed');
        }

        $this->validateSavepointIdentifier($identifier);

        return $this->connection->execute("ROLLBACK TO {$identifier}");
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function releaseSavepoint(string $identifier): Promise
    {
        if (!$this->connection) {
            throw new TransactionError('Transaction has been closed');
        }

        $this->validateSavepointIdentifier($identifier);

        return $this->connection->execute("RELEASE {$identifier}");
    }

    /**
     * @param string $identifier
     * @throws InvalidArgumentException
     */
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
