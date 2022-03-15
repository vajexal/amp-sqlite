<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;

class QueryCommand implements Command
{
    use CommandResponseFactory;

    public function __construct(
        private string $query
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Environment $environment): Promise
    {
        return call(function () use ($environment) {
            $results = $environment->getClient()->query($this->query);

            return $this->createQueryResponse($results, $environment);
        });
    }
}
