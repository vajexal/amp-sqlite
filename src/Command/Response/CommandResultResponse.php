<?php

namespace Vajexal\AmpSQLite\Command\Response;

class CommandResultResponse implements Response
{
    private int $affectedRowCount;

    public function __construct(int $affectedRowCount)
    {
        $this->affectedRowCount = $affectedRowCount;
    }

    public function getAffectedRowCount(): int
    {
        return $this->affectedRowCount;
    }
}
