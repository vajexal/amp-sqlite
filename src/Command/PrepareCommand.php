<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use Amp\Sql\QueryError;
use Vajexal\AmpSQLite\Command\Response\ErrorResponse;
use Vajexal\AmpSQLite\Command\Response\StatementResponse;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;

class PrepareCommand implements Command
{
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
            $statement = $environment->getClient()->prepare($this->query);

            if (!$statement) {
                return new ErrorResponse(QueryError::class, $environment->getClient()->lastErrorMsg());
            }

            $statementId = $environment->addStatement($statement);

            return new StatementResponse($statementId, $this->query);
        });
    }
}
