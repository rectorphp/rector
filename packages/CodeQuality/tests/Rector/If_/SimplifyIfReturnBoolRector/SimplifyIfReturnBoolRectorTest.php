<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector;

use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyIfReturnBoolRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [
                __DIR__ . '/Wrong/wrong.php.inc',
                __DIR__ . '/Wrong/wrong2.php.inc',
                __DIR__ . '/Wrong/wrong3.php.inc',
                __DIR__ . '/Wrong/wrong4.php.inc',
                __DIR__ . '/Wrong/wrong5.php.inc',
                __DIR__ . '/Wrong/wrong6.php.inc',
                __DIR__ . '/Wrong/wrong7.php.inc',
                __DIR__ . '/Wrong/wrong8.php.inc',
                __DIR__ . '/Wrong/wrong9.php.inc',
                __DIR__ . '/Wrong/wrong10.php.inc',
            ]
        );
    }

    public function getRectorClass(): string
    {
        return SimplifyIfReturnBoolRector::class;
    }
}
