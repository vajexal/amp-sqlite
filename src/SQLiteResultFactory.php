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
     * @throws FailureException
     * @throws QueryError
     */
    private function createQueryResultFromResponse(Response $response): SQLiteCommandResult|SQLiteResultSet
    {
        return match (true) {
            $response instanceof QueryErrorResponse => throw new QueryError($response->getMessage()),
            $response instanceof FailureExceptionResponse => throw new FailureException($response->getMessage()),
            $response instanceof CommandResultResponse => new SQLiteCommandResult($response->getAffectedRowCount()),
            $response instanceof ResultSetResponse => new SQLiteResultSet($response->getResults()),
            default => throw new FailureException('unknown response ' . \get_class($response)),
        };
    }

    /**
     * @throws FailureException
     * @throws QueryError
     */
    private function createStatementFromResponse(Response $response): SQLiteStatement
    {
        return match (true) {
            $response instanceof QueryErrorResponse => throw new QueryError($response->getMessage()),
            $response instanceof FailureExceptionResponse => throw new FailureException($response->getMessage()),
            $response instanceof StatementResponse => new SQLiteStatement($this->driver, $response->getStatementId(), $response->getQuery()),
            default => throw new FailureException('unknown response ' . \get_class($response)),
        };
    }
}
