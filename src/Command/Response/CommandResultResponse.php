<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command\Response;

class CommandResultResponse implements Response
{
    public function __construct(
        private int $affectedRowCount
    ) {
    }

    public function getAffectedRowCount(): int
    {
        return $this->affectedRowCount;
    }
}
