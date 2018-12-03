<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector;

use Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ForeachToInArrayRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [
                __DIR__ . '/Wrong/wrong.php.inc',
                __DIR__ . '/Wrong/wrong2.php.inc',
                __DIR__ . '/Wrong/wrong3.php.inc',
                __DIR__ . '/Wrong/wrong4.php.inc',
            ]
        );
    }

    public function getRectorClass(): string
    {
        return ForeachToInArrayRector::class;
    }
}
