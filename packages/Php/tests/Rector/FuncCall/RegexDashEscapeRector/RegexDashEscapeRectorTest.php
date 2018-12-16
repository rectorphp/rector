<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\RegexDashEscapeRector;

use Rector\Php\Rector\FuncCall\RegexDashEscapeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RegexDashEscapeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/method_call.php.inc',
            __DIR__ . '/Fixture/variable.php.inc',
            __DIR__ . '/Fixture/multiple_variables.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return RegexDashEscapeRector::class;
    }
}
