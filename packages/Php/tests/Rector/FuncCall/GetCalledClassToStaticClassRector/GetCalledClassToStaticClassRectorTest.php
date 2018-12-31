<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\GetCalledClassToStaticClassRector;

use Rector\Php\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class GetCalledClassToStaticClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return GetCalledClassToStaticClassRector::class;
    }
}
