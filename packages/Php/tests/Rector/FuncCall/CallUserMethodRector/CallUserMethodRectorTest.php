<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CallUserMethodRector;

use Rector\Php\Rector\FuncCall\CallUserMethodRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see https://www.mail-archive.com/php-dev@lists.php.net/msg11576.html
 */
final class CallUserMethodRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return CallUserMethodRector::class;
    }
}
