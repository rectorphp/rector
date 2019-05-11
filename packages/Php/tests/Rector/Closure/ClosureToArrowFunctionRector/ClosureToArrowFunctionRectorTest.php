<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Closure\ClosureToArrowFunctionRector;

use Rector\Php\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ClosureToArrowFunctionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        if (! class_exists('PhpParser\Node\Expr\ArrowFunction')) {
            $this->markTestSkipped('Wait on php-parser release');
        }

        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/referenced_but_not_used.php.inc',
            // skip
            __DIR__ . '/Fixture/skip_no_return.php.inc',
            __DIR__ . '/Fixture/skip_referenced_value.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ClosureToArrowFunctionRector::class;
    }
}
