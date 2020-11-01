<?php

namespace Vajexal\AmpSQLite;

use Amp\Parallel\Context\StatusError;
use Amp\Parallel\Sync\SynchronizationError;
use Amp\Promise;
use Amp\Sql\ConnectionException;
use Amp\Sql\Link;
use LogicException;
use Throwable;
use Vajexal\AmpSQLite\Command\ExecuteCommand;
use Vajexal\AmpSQLite\Command\PrepareCommand;
use Vajexal\AmpSQLite\Command\QueryCommand;
use function Amp\call;

class SQLiteConnection implements Link
{
    use SQLiteResultFactory;

    private SQLiteDriver $driver;

    public function __construct(SQLiteDriver $driver)
    {
        $this->driver = $driver;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function query(string $sql): Promise
    {
        return call(function () use ($sql) {
            try {
                $command = new QueryCommand($sql);

                $response = yield $this->driver->send($command);

                return $this->createQueryResultFromResponse($response);
            } catch (StatusError | SynchronizationError $e) {
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            }
        });
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function prepare(string $sql): Promise
    {
        return call(function () use ($sql) {
            try {
                $command = new PrepareCommand($sql);

                $response = yield $this->driver->send($command);

                return $this->createStatementFromResponse($response);
            } catch (StatusError | SynchronizationError $e) {
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            }
        });
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function execute(string $sql, array $params = []): Promise
    {
        return call(function () use ($sql, $params) {
            try {
                $command = new ExecuteCommand($sql, $params);

                $response = yield $this->driver->send($command);

                return $this->createQueryResultFromResponse($response);
            } catch (StatusError | SynchronizationError $e) {
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            }
        });
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function beginTransaction(int $isolation = SQLiteTransaction::ISOLATION_DEFERRED): Promise
    {
        return call(function () use ($isolation) {
            if (empty(SQLiteTransaction::ISOLATION_MAP[$isolation])) {
                throw new LogicException("Invalid isolation level {$isolation}");
            }

            yield $this->execute('BEGIN ' . SQLiteTransaction::ISOLATION_MAP[$isolation]);

            return new SQLiteTransaction($this, $isolation);
        });
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        Promise\rethrow(call(function () {
            try {
                if (!$this->isAlive()) {
                    return;
                }

                yield $this->driver->close();
            } catch (StatusError $e) {
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            } catch (SynchronizationError $e) {
                // It's ok
            }
        }));
    }

    /**
     * @inheritDoc
     */
    public function isAlive(): bool
    {
        return $this->driver->isAlive();
    }

    /**
     * @inheritDoc
     */
    public function getLastUsedAt(): int
    {
        return $this->driver->getLastUsedAt();
    }
}
