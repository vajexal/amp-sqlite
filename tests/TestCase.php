<?php

namespace Vajexal\AmpSQLite\Tests;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Vajexal\AmpSQLite\SQLiteResultSet;
use function Amp\call;

class TestCase extends AsyncTestCase
{
    protected function compareResultSets(array $expected, SQLiteResultSet $actual): Promise
    {
        return call(function () use ($expected, $actual) {
            foreach ($expected as $row) {
                $this->assertTrue(yield $actual->advance());
                $this->assertEquals($row, $actual->getCurrent());
            }

            $this->assertFalse(yield $actual->advance());
        });
    }
}
