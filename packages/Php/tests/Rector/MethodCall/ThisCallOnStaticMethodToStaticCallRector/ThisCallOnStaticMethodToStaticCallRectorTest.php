<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;

use Rector\Php\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ThisCallOnStaticMethodToStaticCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/another_call.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ThisCallOnStaticMethodToStaticCallRector::class;
    }
}
