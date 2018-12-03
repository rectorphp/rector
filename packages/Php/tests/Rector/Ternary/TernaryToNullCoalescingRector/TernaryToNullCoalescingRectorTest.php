<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\Ternary\TernaryToNullCoalescingRector;

use Rector\Php\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Some tests copied from:
 * https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/0db4f91088a3888a7c8b26e5a36fba53c0d9507c#diff-02f477b178d0dc5b25ac05ab3b59e7c7
 * https://github.com/slevomat/coding-standard/blob/master/tests/Sniffs/ControlStructures/data/requireNullCoalesceOperatorErrors.fixed.php
 */
final class TernaryToNullCoalescingRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'], [
                __DIR__ . '/Wrong/wrong2.php.inc',
                __DIR__ . '/Correct/correct2.php.inc',
            ], [
                __DIR__ . '/Wrong/wrong3.php.inc',
                __DIR__ . '/Correct/correct3.php.inc',
            ], [__DIR__ . '/Wrong/wrong4.php.inc', __DIR__ . '/Correct/correct4.php.inc']]
        );
    }

    public function getRectorClass(): string
    {
        return TernaryToNullCoalescingRector::class;
    }
}
