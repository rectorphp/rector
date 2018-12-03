<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\MultiDirnameRector;

use Rector\Php\Rector\FuncCall\MultiDirnameRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Some tests copied from:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/pull/3826/files
 */
final class MultiDirnameRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'], [
                __DIR__ . '/Wrong/wrong2.php.inc',
                __DIR__ . '/Correct/correct2.php.inc',
            ], [__DIR__ . '/Wrong/wrong3.php.inc', __DIR__ . '/Correct/correct3.php.inc']]
        );
    }

    public function getRectorClass(): string
    {
        return MultiDirnameRector::class;
    }
}
