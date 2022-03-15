<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

use Amp\Parallel\Context;
use Amp\Parallel\Sync\SynchronizationError;
use Amp\Promise;
use Amp\Sql\TransientResource;
use Amp\Sync\LocalMutex;
use Amp\Sync\Mutex;
use Amp\TimeoutException;
use Vajexal\AmpSQLite\Command\CloseCommand;
use Vajexal\AmpSQLite\Command\Command;
use Vajexal\AmpSQLite\Command\Response\Response;
use function Amp\call;
use function Amp\Sync\synchronized;

class SQLiteDriver implements TransientResource
{
    private const CONTEXT_CLOSE_TIMEOUT = 50;

    private Context\Context $context;
    private int             $lastUsedAt = 0;
    private Mutex           $mutex;

    private function __construct()
    {
        $this->mutex = new LocalMutex();
    }

    public function __destruct()
    {
        Promise\rethrow($this->close());
    }

    /**
     * @return Promise<self>
     */
    public static function create(string $filename, ?int $flags = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, ?string $encryptionKey = ''): Promise
    {
        return call(function () use ($filename, $flags, $encryptionKey) {
            $driver = new self();

            $driver->context = yield Context\run(__DIR__ . DIRECTORY_SEPARATOR . 'sqlite-worker.php');
            $request = new OpenConnectionRequest($filename, $flags, $encryptionKey);
            yield $driver->context->send($request);

            $driver->lastUsedAt = \time();

            return $driver;
        });
    }

    /**
     * @return Promise<Response>
     */
    public function send(Command $command): Promise
    {
        return synchronized($this->mutex, function () use ($command) {
            if (!$this->isAlive()) {
                throw new SynchronizationError('Process unexpectedly exited');
            }

            yield $this->context->send($command);

            $response = yield $this->context->receive();

            \assert($response instanceof Response);

            $this->lastUsedAt = \time();

            return $response;
        });
    }

    public function close(): Promise
    {
        return synchronized($this->mutex, function () {
            if (!$this->isAlive()) {
                return;
            }

            $command = new CloseCommand();
            yield $this->context->send($command);

            try {
                yield Promise\timeout($this->context->join(), self::CONTEXT_CLOSE_TIMEOUT);
            } catch (TimeoutException) {
                $this->context->kill();
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function isAlive(): bool
    {
        return $this->context->isRunning();
    }

    /**
     * @inheritDoc
     */
    public function getLastUsedAt(): int
    {
        return $this->lastUsedAt;
    }
}
