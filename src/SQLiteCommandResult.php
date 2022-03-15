<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite;

use Amp\Sql\CommandResult;

class SQLiteCommandResult implements CommandResult
{
    public function __construct(
        private int $affectedRowCount
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAffectedRowCount(): int
    {
        return $this->affectedRowCount;
    }
}
