<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Function_\ReservedFnFunctionRector;

use PhpParser\Parser\Tokens;
use Rector\Php\Rector\Function_\ReservedFnFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReservedFnFunctionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        if (defined(Tokens::class . '::T_FN')) {
            $this->markTestSkipped('fn is reserved name in PHP 7.4');
        }

        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ReservedFnFunctionRector::class;
    }
}
