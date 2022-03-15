<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use Vajexal\AmpSQLite\Environment\Environment;
use Vajexal\AmpSQLite\Exception\CloseConnectionException;
use function Amp\call;

class CloseCommand implements Command
{
    /**
     * @inheritDoc
     */
    public function execute(Environment $environment): Promise
    {
        return call(function () use ($environment) {
            $environment->getClient()->close();

            throw new CloseConnectionException();
        });
    }
}
