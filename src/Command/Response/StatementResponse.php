<?php

namespace Vajexal\AmpSQLite\Command\Response;

class StatementResponse implements Response
{
    private int $statementId;
    private string $query;

    public function __construct(int $statementId, string $query)
    {
        $this->statementId = $statementId;
        $this->query = $query;
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
