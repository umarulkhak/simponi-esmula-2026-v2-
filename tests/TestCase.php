<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private int $qaOutputBufferLevel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->qaOutputBufferLevel = ob_get_level();
        config(['debugbar.enabled' => false]);
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->qaOutputBufferLevel) {
            ob_end_clean();
        }

        parent::tearDown();
    }
}
