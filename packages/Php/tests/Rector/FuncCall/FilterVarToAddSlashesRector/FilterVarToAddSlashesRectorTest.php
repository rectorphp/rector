<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\FilterVarToAddSlashesRector;

use Rector\Php\Rector\FuncCall\FilterVarToAddSlashesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FilterVarToAddSlashesRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return FilterVarToAddSlashesRector::class;
    }
}
