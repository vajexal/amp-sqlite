<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use Vajexal\AmpSQLite\Command\Response\FailureExceptionResponse;
use Vajexal\AmpSQLite\Command\Response\SuccessResponse;
use Vajexal\AmpSQLite\Environment\Environment;
use function Amp\call;

class StatementCloseCommand implements Command
{
    private int $statementId;

    public function __construct(int $statementId)
    {
        $this->statementId = $statementId;
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

            $statement->close();

            $environment->removeStatement($this->statementId);

            return new SuccessResponse;
        });
    }
}
