<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure APP_KEY is set correctly for tests (override any file path references)
        if (env('APP_KEY') && str_starts_with(env('APP_KEY'), '/')) {
            // If APP_KEY is a file path, read it
            if (file_exists(env('APP_KEY'))) {
                $key = trim(file_get_contents(env('APP_KEY')));
                config(['app.key' => $key]);
            }
        }
    }
}
