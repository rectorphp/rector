<?php declare(strict_types=1);

namespace Rector\PhpParser\Tests\Rector\UseWithAliasRector;

use Rector\PhpParser\Rector\UseWithAliasRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UseWithAliasRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return UseWithAliasRector::class;
    }
}
