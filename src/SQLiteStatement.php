<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

use Amp\Parallel\Context\StatusError;
use Amp\Parallel\Sync\SynchronizationError;
use Amp\Promise;
use Amp\Sql\ConnectionException;
use Amp\Sql\Statement;
use Throwable;
use Vajexal\AmpSQLite\Command\StatementCloseCommand;
use Vajexal\AmpSQLite\Command\StatementExecuteCommand;
use function Amp\call;

class SQLiteStatement implements Statement
{
    use SQLiteResultFactory;

    private SQLiteDriver $driver;
    private int          $statementId;
    private string       $query;

    public function __construct(SQLiteDriver $driver, int $statementId, string $query)
    {
        $this->driver      = $driver;
        $this->statementId = $statementId;
        $this->query       = $query;
    }

    public function __destruct()
    {
        Promise\rethrow($this->close());
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function execute(array $params = []): Promise
    {
        return call(function () use ($params) {
            try {
                $command = new StatementExecuteCommand($this->statementId, $params);

                $response = yield $this->driver->send($command);

                return $this->createQueryResultFromResponse($response);
            } catch (StatusError | SynchronizationError $e) {
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            }
        });
    }

    private function close(): Promise
    {
        return call(function () {
            try {
                if (!$this->isAlive()) {
                    return;
                }

                $command = new StatementCloseCommand($this->statementId);

                yield $this->driver->send($command);
            } catch (StatusError $e) {
                throw new ConnectionException($e->getMessage(), $e->getCode(), $e);
            } catch (SynchronizationError $e) {
                // It's ok
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): string
    {
        return $this->query;
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
