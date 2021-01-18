<?php

declare(strict_types=1);

namespace Vajexal\AmpSQLite\Tests;

use Amp\Parallel\Context\Context;
use Amp\Parallel\Context\ContextFactory;
use Amp\Parallel\Context\Process;
use Amp\Promise;
use function Amp\Parallel\Context\factory;

class ProcessSQLiteTest extends SQLiteTest
{
    protected function setUp(): void
    {
        parent::setUp();

        factory(new class implements ContextFactory {
            public function create($script): Context
            {
                return new Process($script);
            }

            public function run($script): Promise
            {
                return Process::run($script);
            }
        });
    }
}
