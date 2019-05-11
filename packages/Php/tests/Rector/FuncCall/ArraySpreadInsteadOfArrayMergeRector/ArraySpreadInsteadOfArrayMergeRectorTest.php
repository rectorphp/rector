<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;

use Rector\Php\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArraySpreadInsteadOfArrayMergeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        if (! class_exists('PhpParser\Node\Expr\ArrowFunction')) {
            $this->markTestSkipped('Wait on php-parser release');
        }

        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/iterator_to_array.php.inc',
            __DIR__ . '/Fixture/simple_array_merge.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ArraySpreadInsteadOfArrayMergeRector::class;
    }
}
