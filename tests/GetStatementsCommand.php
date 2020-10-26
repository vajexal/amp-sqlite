<?php

namespace Vajexal\AmpSQLite\Tests;

use Amp\Promise;
use Vajexal\AmpSQLite\Command\Command;
use Vajexal\AmpSQLite\Command\Response\CommandResultResponse;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;

class GetStatementsCommand implements Command
{
    public function execute(Environment $environment): Promise
    {
        return call(function () use ($environment) {
            $statements = getPrivateProperty($environment, 'statements');
            return new CommandResultResponse(\count($statements));
        });
    }
}
