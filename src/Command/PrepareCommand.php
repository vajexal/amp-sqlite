<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use Vajexal\AmpSQLite\Command\Response\QueryErrorResponse;
use Vajexal\AmpSQLite\Command\Response\StatementResponse;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;

class PrepareCommand implements Command
{
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
            $statement = $environment->getClient()->prepare($this->query);

            if (!$statement) {
                return new QueryErrorResponse($environment->getClient()->lastErrorMsg());
            }

            $statementId = $environment->addStatement($statement);

            return new StatementResponse($statementId, $this->query);
        });
    }
}
