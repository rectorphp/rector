<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector;

use Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReplaceAssertArraySubsetRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/issue_2069.php.inc',
            __DIR__ . '/Fixture/issue_2237.php.inc',
            __DIR__ . '/Fixture/multilevel_array.php.inc',
            __DIR__ . '/Fixture/variable.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ReplaceAssertArraySubsetRector::class;
    }
}
