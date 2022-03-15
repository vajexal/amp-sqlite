<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

use Amp\Parallel\Sync\Channel;
use Generator;
use SQLite3;
use Vajexal\AmpSQLite\Command\Command;
use Vajexal\AmpSQLite\Environment\Environment;
use Vajexal\AmpSQLite\Exception\CloseConnectionException;

return function (Channel $channel): Generator {
    $request = yield $channel->receive();

    \assert($request instanceof OpenConnectionRequest);

    $client      = new SQLite3($request->getFilename(), $request->getFlags(), $request->getEncryptionKey());
    $environment = new Environment($client);

    try {
        while (true) {
            $command = yield $channel->receive();

            \assert($command instanceof Command);

            $response = yield $command->execute($environment);

            yield $channel->send($response);
        }
    } catch (CloseConnectionException) {
        return;
    }
};
