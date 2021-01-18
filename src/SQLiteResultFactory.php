<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

use Amp\Sql\FailureException;
use Throwable;
use Vajexal\AmpSQLite\Command\Response\CommandResultResponse;
use Vajexal\AmpSQLite\Command\Response\ErrorResponse;
use Vajexal\AmpSQLite\Command\Response\Response;
use Vajexal\AmpSQLite\Command\Response\ResultSetResponse;
use Vajexal\AmpSQLite\Command\Response\StatementResponse;

trait SQLiteResultFactory
{
    /**
     * @param Response $response
     * @return SQLiteCommandResult|SQLiteResultSet
     * @throws FailureException
     * @throws Throwable
     */
    private function createQueryResultFromResponse(Response $response)
    {
        switch (true) {
            case $response instanceof ErrorResponse:
                throw $response->getThrowable();
            case $response instanceof CommandResultResponse:
                return new SQLiteCommandResult($response->getAffectedRowCount());
            case $response instanceof ResultSetResponse:
                return new SQLiteResultSet($response->getResults());
            default:
                throw new FailureException('unknown response ' . \get_class($response));
        }
    }

    /**
     * @param Response $response
     * @return SQLiteStatement
     * @throws FailureException
     * @throws Throwable
     */
    private function createStatementFromResponse(Response $response): SQLiteStatement
    {
        switch (true) {
            case $response instanceof ErrorResponse:
                throw $response->getThrowable();
            case $response instanceof StatementResponse:
                return new SQLiteStatement($this->driver, $response->getStatementId(), $response->getQuery());
            default:
                throw new FailureException('unknown response ' . \get_class($response));
        }
    }
}
