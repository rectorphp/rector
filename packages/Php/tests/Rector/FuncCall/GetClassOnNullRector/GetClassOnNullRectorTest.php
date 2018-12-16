<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\GetClassOnNullRector;

use Rector\Php\Rector\FuncCall\GetClassOnNullRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetClassOnNullRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/tricky_cases.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return GetClassOnNullRector::class;
    }
}
