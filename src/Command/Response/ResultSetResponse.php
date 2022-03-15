<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command\Response;

class ResultSetResponse implements Response
{
    public function __construct(
        private array $results
    ) {
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
