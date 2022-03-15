<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command\Response;

class StatementResponse implements Response
{
    public function __construct(
        private int $statementId,
        private string $query
    ) {
    }

    public function getStatementId(): int
    {
        return $this->statementId;
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
