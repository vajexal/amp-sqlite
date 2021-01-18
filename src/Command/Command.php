<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use Vajexal\AmpSQLite\Command\Response\Response;
use Vajexal\AmpSQLite\Environment\Environment;

interface Command
{
    /**
     * @param Environment $environment
     * @return Promise<Response>
     */
    public function execute(Environment $environment): Promise;
}
