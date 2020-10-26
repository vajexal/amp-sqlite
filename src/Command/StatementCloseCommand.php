<?php

namespace Vajexal\AmpSQLite\Command;

use Amp\Promise;
use LogicException;
use Vajexal\AmpSQLite\Command\Response\ErrorResponse;
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
                return new ErrorResponse(LogicException::class, "could not find statement {$this->statementId}");
            }

            $statement->close();

            $environment->removeStatement($this->statementId);

            return new SuccessResponse;
        });
    }
}
