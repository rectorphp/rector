<?php declare(strict_types=1);

namespace Rector\Legacy\Tests\Rector\ClassMethod\ChangeSingletonToServiceRector;

use Rector\Legacy\Rector\ClassMethod\ChangeSingletonToServiceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ChangeSingletonToServiceRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/static_variable.php.inc',
            __DIR__ . '/Fixture/protected_construct.php.inc',
            __DIR__ . '/Fixture/non_empty_protected_construct.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ChangeSingletonToServiceRector::class;
    }
}
