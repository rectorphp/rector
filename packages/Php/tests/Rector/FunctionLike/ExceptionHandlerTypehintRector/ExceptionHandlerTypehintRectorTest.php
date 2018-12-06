<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\ExceptionHandlerTypehintRector;

use Rector\Php\Rector\FunctionLike\ExceptionHandlerTypehintRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExceptionHandlerTypehintRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            //            __DIR__ . '/Fixture/fixture_nullable.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return ExceptionHandlerTypehintRector::class;
    }
}
