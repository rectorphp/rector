<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector;

use Rector\Php\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameMktimeWithoutArgsToTimeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return RenameMktimeWithoutArgsToTimeRector::class;
    }
}
