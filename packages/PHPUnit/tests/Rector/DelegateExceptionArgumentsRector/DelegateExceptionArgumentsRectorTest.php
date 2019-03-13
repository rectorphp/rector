<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\DelegateExceptionArgumentsRector;

use Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class DelegateExceptionArgumentsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/message.php.inc',
            __DIR__ . '/Fixture/regexp.php.inc',
            __DIR__ . '/Fixture/self_nested.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return DelegateExceptionArgumentsRector::class;
    }
}
