<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Foreach_\SimplifyForeachInstanceOfRector;

use Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyForeachInstanceOfRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SimplifyForeachInstanceOfRector::class;
    }
}
