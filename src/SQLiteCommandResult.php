<?php

namespace Vajexal\AmpSQLite;

use Amp\Sql\CommandResult;

class SQLiteCommandResult implements CommandResult
{
    private int $affectedRowCount;

    public function __construct(int $affectedRowCount)
    {
        $this->affectedRowCount = $affectedRowCount;
    }

    /**
     * @inheritDoc
     */
    public function getAffectedRowCount(): int
    {
        return $this->affectedRowCount;
    }
}
