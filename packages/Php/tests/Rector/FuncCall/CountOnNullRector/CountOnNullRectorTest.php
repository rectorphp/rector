<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector;

use Rector\Php\Rector\FuncCall\CountOnNullRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CountOnNullRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Integration/array_countable_class.php.inc',
            __DIR__ . '/Integration/countable_annotated_params.php.inc',
            __DIR__ . '/Integration/false_true_class.php.inc',
            __DIR__ . '/Integration/on_null.php.inc',
            __DIR__ . '/Integration/property.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return CountOnNullRector::class;
    }
}
