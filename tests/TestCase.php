<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $compiledViewPath = storage_path('framework/testing/views');
        File::ensureDirectoryExists($compiledViewPath);
        config(['view.compiled' => $compiledViewPath]);
    }
}
