<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Environment;

use SQLite3;
use SQLite3Stmt;

class Environment
{
    private SQLite3 $client;
    private array   $statements;

    public function __construct(SQLite3 $client)
    {
        $this->client = $client;
    }

    public function getClient(): SQLite3
    {
        return $this->client;
    }

    public function addStatement(SQLite3Stmt $statement): int
    {
        $statementId = \spl_object_id($statement);

        $this->statements[$statementId] = $statement;

        return $statementId;
    }

    public function getStatement(int $statementId): ?SQLite3Stmt
    {
        return $this->statements[$statementId] ?? null;
    }

    public function removeStatement(int $statementId): void
    {
        unset($this->statements[$statementId]);
    }
}
