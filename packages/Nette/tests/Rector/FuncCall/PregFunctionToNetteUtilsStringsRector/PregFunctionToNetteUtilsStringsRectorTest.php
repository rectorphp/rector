<?php declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;

use Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PregFunctionToNetteUtilsStringsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/preg_match.php.inc',
            __DIR__ . '/Fixture/preg_match_all.php.inc',
            __DIR__ . '/Fixture/preg_split.php.inc',
            __DIR__ . '/Fixture/preg_replace.php.inc',
            __DIR__ . '/Fixture/preg_replace_callback.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return PregFunctionToNetteUtilsStringsRector::class;
    }
}
