<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector;

use Rector\Php\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddLiteralSeparatorToNumberRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip_non_dec_simple_float_numbers.php.inc',
            __DIR__ . '/Fixture/skip_hexadecimal.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AddLiteralSeparatorToNumberRector::class;
    }
}
