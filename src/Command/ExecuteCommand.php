<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use Vajexal\AmpSQLite\Command\Response\QueryErrorResponse;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;

class ExecuteCommand implements Command
{
    use CommandResponseFactory, StatementBinding;

    public function __construct(
        private string $query,
        private array $bindings
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

            $this->addBindings($statement);

            $results = $statement->execute();

            return $this->createQueryResponse($results, $environment);
        });
    }
}
