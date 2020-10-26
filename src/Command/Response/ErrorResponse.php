<?php

namespace Vajexal\AmpSQLite\Command\Response;

use Throwable;

class ErrorResponse implements Response
{
    private string $throwable;
    private string $message;

    public function __construct(string $throwable, string $message)
    {
        $this->throwable = $throwable;
        $this->message = $message;
    }

    public function getThrowable(): Throwable
    {
        return new $this->throwable($this->message);
    }
}
