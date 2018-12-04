<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\PowToExpRector;

use Rector\Php\Rector\FuncCall\PowToExpRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Some tests copied from:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/14660432d9d0b66bf65135d793b52872cc6eccbc#diff-b412676c923661ef450f4a0903c5442a
 */
final class PowToExpRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return PowToExpRector::class;
    }
}
