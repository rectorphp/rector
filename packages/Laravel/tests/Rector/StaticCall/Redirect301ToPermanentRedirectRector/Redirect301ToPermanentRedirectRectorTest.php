<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\Redirect301ToPermanentRedirectRector;

use Rector\Laravel\Rector\StaticCall\Redirect301ToPermanentRedirectRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Redirect301ToPermanentRedirectRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return Redirect301ToPermanentRedirectRector::class;
    }
}
