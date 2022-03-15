<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

use Amp\Promise;
use function Amp\call;

/**
 * @return Promise<SQLiteConnection>
 */
function connect(string $filename, int $flags = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE, string $encryptionKey = ''): Promise
{
    return call(function () use ($filename, $flags, $encryptionKey) {
        $driver = yield SQLiteDriver::create($filename, $flags, $encryptionKey);

        return new SQLiteConnection($driver);
    });
}
