<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

use Amp\Sql\FailureException;
use Amp\Sql\QueryError;
use Vajexal\AmpSQLite\Command\Response\CommandResultResponse;
use Vajexal\AmpSQLite\Command\Response\FailureExceptionResponse;
use Vajexal\AmpSQLite\Command\Response\QueryErrorResponse;
use Vajexal\AmpSQLite\Command\Response\Response;
use Vajexal\AmpSQLite\Command\Response\ResultSetResponse;
use Vajexal\AmpSQLite\Command\Response\StatementResponse;

trait SQLiteResultFactory
{
    /**
     * @param Response $response
     * @return SQLiteCommandResult|SQLiteResultSet
     * @throws FailureException
     * @throws QueryError
     */
    private function createQueryResultFromResponse(Response $response)
    {
        switch (true) {
            case $response instanceof QueryErrorResponse:
                throw new QueryError($response->getMessage());
            case $response instanceof FailureExceptionResponse:
                throw new FailureException($response->getMessage());
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
     * @throws QueryError
     */
    private function createStatementFromResponse(Response $response): SQLiteStatement
    {
        switch (true) {
            case $response instanceof QueryErrorResponse:
                throw new QueryError($response->getMessage());
            case $response instanceof FailureExceptionResponse:
                throw new FailureException($response->getMessage());
            case $response instanceof StatementResponse:
                return new SQLiteStatement($this->driver, $response->getStatementId(), $response->getQuery());
            default:
                throw new FailureException('unknown response ' . \get_class($response));
        }
    }
}
