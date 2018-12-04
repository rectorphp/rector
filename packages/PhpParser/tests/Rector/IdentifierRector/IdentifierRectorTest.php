<?php declare(strict_types=1);

namespace Rector\PhpParser\Tests\Rector\IdentifierRector;

use Rector\PhpParser\Rector\IdentifierRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class IdentifierRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Wrong/wrong3.php.inc',
            __DIR__ . '/Wrong/wrong4.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return IdentifierRector::class;
    }
}
