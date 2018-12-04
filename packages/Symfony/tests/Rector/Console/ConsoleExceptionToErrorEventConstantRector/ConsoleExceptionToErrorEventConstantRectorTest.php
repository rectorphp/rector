<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExceptionToErrorEventConstantRector;

use Rector\Symfony\Rector\Console\ConsoleExceptionToErrorEventConstantRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConsoleExceptionToErrorEventConstantRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return ConsoleExceptionToErrorEventConstantRector::class;
    }
}
