<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;

use Rector\Php\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArraySpreadInsteadOfArrayMergeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        if (! class_exists('PhpParser\Node\Expr\ArrowFunction')) {
            $this->markTestSkipped('Waits on php-parser release');
        }

        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/iterator_to_array.php.inc',
            __DIR__ . '/Fixture/integer_keys.php.inc',
            // see caveat: https://twitter.com/nikita_ppv/status/1126470222838366209
            __DIR__ . '/Fixture/skip_simple_array_merge.php.inc',
            __DIR__ . '/Fixture/skip_string_keys.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ArraySpreadInsteadOfArrayMergeRector::class;
    }
}
