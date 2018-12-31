<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\StaticCall\ExportToReflectionFunctionRector;

use Rector\Php\Rector\StaticCall\ExportToReflectionFunctionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ExportToReflectionFunctionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ExportToReflectionFunctionRector::class;
    }
}
