<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector;

use Rector\Php\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringClassNameToClassConstantRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/skip_error.php.inc',
            __DIR__ . '/Fixture/skip_sensitive.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return StringClassNameToClassConstantRector::class;
    }
}
