<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Name\ReservedObjectRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @covers \Rector\Php\Rector\Name\ReservedObjectRector
 */
final class ReservedObjectRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([[__DIR__ . '/Wrong/ReservedObject.php', __DIR__ . '/Correct/correct.php.inc']]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
