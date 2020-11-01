<?php

namespace Vajexal\AmpSQLite\Tests\Command;

use Amp\Promise;
use Vajexal\AmpSQLite\Command\Command;
use Vajexal\AmpSQLite\Command\Response\CommandResultResponse;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;
use function Vajexal\AmpSQLite\Tests\getPrivateProperty;

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
