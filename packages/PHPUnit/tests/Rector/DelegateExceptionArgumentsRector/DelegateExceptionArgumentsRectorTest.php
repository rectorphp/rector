<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\DelegateExceptionArgumentsRector;

use Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class DelegateExceptionArgumentsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [
                __DIR__ . '/Fixture/fixture.php.inc',
                __DIR__ . '/Fixture/fixture2.php.inc',
                __DIR__ . '/Fixture/fixture3.php.inc',
            ]
        );
    }

    public function getRectorClass(): string
    {
        return DelegateExceptionArgumentsRector::class;
    }
}
