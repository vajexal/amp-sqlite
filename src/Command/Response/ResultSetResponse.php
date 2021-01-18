<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command\Response;

class ResultSetResponse implements Response
{
    private array $results;

    public function __construct(array $results)
    {
        $this->results = $results;
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
