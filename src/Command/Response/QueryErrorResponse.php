<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command\Response;

class QueryErrorResponse implements Response
{
    private string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
