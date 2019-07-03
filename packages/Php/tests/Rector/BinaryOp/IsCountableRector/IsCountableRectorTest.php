<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\BinaryOp\IsCountableRector;

use Rector\Php\Rector\BinaryOp\IsCountableRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IsCountableRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        // That method can be provided by `symfony/polyfill-php73` package,
        // or not exists as it's run on PHP < 7.3
        if (! function_exists('is_countable')) {
            $this->doTestFiles([__DIR__ . '/Fixture/fixture71.php.inc']);
        } else {
            $this->doTestFiles([__DIR__ . '/Fixture/fixture73.php.inc']);
        }
    }

    public function getRectorClass(): string
    {
        return IsCountableRector::class;
    }
}
