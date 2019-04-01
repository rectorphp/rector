<?php declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector;

use Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SubstrStrlenFunctionToNetteUtilsStringsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/substr.php.inc', __DIR__ . '/Fixture/strlen.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return SubstrStrlenFunctionToNetteUtilsStringsRector::class;
    }
}
