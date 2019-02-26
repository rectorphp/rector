<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\StringifyDefineRector;

use Rector\Php\Rector\FuncCall\StringifyDefineRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringifyDefineRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return StringifyDefineRector::class;
    }
}
