<?php

namespace Rector\Laravel\Tests\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector\Fixture;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class ExistingSetUp extends TestCase
{
    protected function setUp()
    {
        $value = 1000;
    }

    public function test(): void
    {
        $this->assertEquals('content', \trim(Artisan::output()));
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector\Fixture;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class ExistingSetUp extends TestCase
{
    protected function setUp()
    {
        $value = 1000;
        $this->mockConsoleOutput = false;
    }

    public function test(): void
    {
        $this->assertEquals('content', \trim(Artisan::output()));
    }
}

?>
