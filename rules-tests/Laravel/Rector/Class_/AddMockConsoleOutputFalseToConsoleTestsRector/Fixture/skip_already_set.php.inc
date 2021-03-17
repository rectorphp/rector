<?php

namespace Rector\Tests\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector\Fixture;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class SkipAlreadySet extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockConsoleOutput = false;
    }

    public function test(): void
    {
        $this->assertEquals('content', \trim(Artisan::output()));
    }
}
