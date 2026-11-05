<?php

declare(strict_types=1);

namespace Tests;

use RuntimeException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** RefreshDatabase wipes every table, so only in-memory SQLite is allowed. */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "Refusing to run tests against '{$connection}' ({$database}); they require in-memory SQLite. "
                . 'Check the DB_* entries in phpunit.xml.'
            );
        }
    }
}
