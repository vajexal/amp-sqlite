<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Command\Response;

class QueryErrorResponse implements Response
{
    public function __construct(
        private string $message
    ) {
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
