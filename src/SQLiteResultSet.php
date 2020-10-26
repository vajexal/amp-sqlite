<?php

namespace Vajexal\AmpSQLite;

use Amp\Promise;
use Amp\Sql\ResultSet;
use Amp\Success;

class SQLiteResultSet implements ResultSet
{
    private array $results;
    private int $position = -1;

    public function __construct(array $results)
    {
        $this->results = $results;
    }

    /**
     * @inheritDoc
     */
    public function advance(): Promise
    {
        return new Success(++$this->position < \count($this->results));
    }

    /**
     * @inheritDoc
     */
    public function getCurrent(): array
    {
        return $this->results[$this->position];
    }
}
