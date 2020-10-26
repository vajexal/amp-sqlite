<?php

namespace Vajexal\AmpSQLite\Command;

use SQLite3Stmt;

trait StatementBinding
{
    private function addBindings(SQLite3Stmt $statement)
    {
        foreach ($this->bindings as $key => $value) {
            // https://www.php.net/manual/ru/function.ctype-print.php#123095
            if (\is_string($value) && \ctype_print($value)) {
                $statement->bindValue($key, $value);
            } else {
                $statement->bindValue($key, $value, SQLITE3_BLOB);
            }
        }
    }
}
