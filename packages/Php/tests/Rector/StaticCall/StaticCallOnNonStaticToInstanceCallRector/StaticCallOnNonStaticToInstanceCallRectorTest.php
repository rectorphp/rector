<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;

use Rector\Php\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StaticCallOnNonStaticToInstanceCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/with_constructor.php.inc',
            __DIR__ . '/Fixture/keep.php.inc',
            __DIR__ . '/Fixture/keep_parent_static.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return StaticCallOnNonStaticToInstanceCallRector::class;
    }
}
