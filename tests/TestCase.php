<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Automatically run tenant migrations for SQLite database in tests
        if (config('database.default') === 'sqlite') {
            $this->artisan('migrate', ['--path' => 'database/migrations/tenant']);
        }
    }
}
