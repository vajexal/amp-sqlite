<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use LogicException;
use Vajexal\AmpSQLite\Command\Response\ErrorResponse;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;

class StatementExecuteCommand implements Command
{
    use CommandResponseFactory, StatementBinding;

    private int   $statementId;
    private array $bindings;

    public function __construct(int $statementId, array $bindings)
    {
        $this->statementId = $statementId;
        $this->bindings    = $bindings;
    }

    /**
     * @inheritDoc
     */
    public function execute(Environment $environment): Promise
    {
        return call(function () use ($environment) {
            $statement = $environment->getStatement($this->statementId);

            if (!$statement) {
                return new ErrorResponse(LogicException::class, "could not find statement {$this->statementId}");
            }

            $this->addBindings($statement);

            $results = $statement->execute();

            return $this->createQueryResponse($results, $environment);
        });
    }
}
