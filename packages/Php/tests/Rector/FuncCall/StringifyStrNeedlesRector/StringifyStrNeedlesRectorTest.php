<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\StringifyStrNeedlesRector;

use Rector\Php\Rector\FuncCall\StringifyStrNeedlesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringifyStrNeedlesRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return StringifyStrNeedlesRector::class;
    }
}
