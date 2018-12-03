<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\TryCatch\MultiExceptionCatchRector;

use Rector\Php\Rector\TryCatch\MultiExceptionCatchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MultiExceptionCatchRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return MultiExceptionCatchRector::class;
    }
}
