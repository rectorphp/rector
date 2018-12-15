<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\StringsAssertNakedRector;

use Rector\Php\Rector\FuncCall\StringsAssertNakedRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringsAssertNakedRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return StringsAssertNakedRector::class;
    }
}
