<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use Vajexal\AmpSQLite\Command\Response\FailureExceptionResponse;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;

class StatementExecuteCommand implements Command
{
    use CommandResponseFactory, StatementBinding;

    public function __construct(
        private int $statementId,
        private array $bindings
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(Environment $environment): Promise
    {
        return call(function () use ($environment) {
            $statement = $environment->getStatement($this->statementId);

            if (!$statement) {
                return new FailureExceptionResponse("could not find statement {$this->statementId}");
            }

            $this->addBindings($statement);

            $results = $statement->execute();

            return $this->createQueryResponse($results, $environment);
        });
    }
}
