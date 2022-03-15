<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command;

use Vajexal\AmpSQLite\Command\Response\CommandResultResponse;
use Vajexal\AmpSQLite\Command\Response\QueryErrorResponse;
use Vajexal\AmpSQLite\Command\Response\ResultSetResponse;
use Vajexal\AmpSQLite\Environment\Environment;

trait CommandResponseFactory
{
    private function createQueryResponse(\SQLite3Result|bool $results, Environment $environment): ResultSetResponse|CommandResultResponse|QueryErrorResponse
    {
        if (!$results) {
            return new QueryErrorResponse($environment->getClient()->lastErrorMsg());
        }

        if ($results === true) {
            return new CommandResultResponse($environment->getClient()->changes());
        }

        // https://www.php.net/manual/ru/sqlite3result.fetcharray.php#120631
        if ($results->numColumns() > 0) {
            $rows = [];

            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }

            $results->finalize();

            return new ResultSetResponse($rows);
        }

        $results->finalize();

        return new CommandResultResponse($environment->getClient()->changes());
    }
}
