<?php

namespace Rector\Tests\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector\Fixture;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class NewArtisan extends TestCase
{
    public function test(): void
    {
        $this->assertEquals('content', \trim((new Artisan())::output()));
    }
}

?>
-----
<?php

namespace Rector\Tests\Laravel\Rector\Class_\AddMockConsoleOutputFalseToConsoleTestsRector\Fixture;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\TestCase;

final class NewArtisan extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockConsoleOutput = false;
    }
    public function test(): void
    {
        $this->assertEquals('content', \trim((new Artisan())::output()));
    }
}

?>
