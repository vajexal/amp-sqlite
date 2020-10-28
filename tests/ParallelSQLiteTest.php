<?php

namespace Vajexal\AmpSQLite\Tests;

use Amp\Parallel\Context\Context;
use Amp\Parallel\Context\ContextFactory;
use Amp\Parallel\Context\Parallel;
use Amp\Promise;
use function Amp\Parallel\Context\factory;

class ParallelSQLiteTest extends SQLiteTest
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!\extension_loaded('parallel')) {
            $this->markTestSkipped('parallel extension is not loaded');
        }

        factory(new class implements ContextFactory {
            public function create($script): Context
            {
                return new Parallel($script);
            }

            public function run($script): Promise
            {
                return Parallel::run($script);
            }
        });
    }
}
