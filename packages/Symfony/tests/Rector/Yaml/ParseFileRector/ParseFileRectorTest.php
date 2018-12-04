<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Yaml\ParseFileRector;

use Rector\Symfony\Rector\Yaml\ParseFileRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ParseFileRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Wrong/wrong3.php.inc',
        ]);
    }

    public function getRectorClass(): string
    {
        return ParseFileRector::class;
    }
}
