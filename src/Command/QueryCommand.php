<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;

class QueryCommand implements Command
{
    use CommandResponseFactory;

    private string $query;

    public function __construct(string $query)
    {
        $this->query = $query;
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
