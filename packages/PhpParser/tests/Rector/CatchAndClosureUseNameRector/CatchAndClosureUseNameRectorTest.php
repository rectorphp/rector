<?php declare(strict_types=1);

namespace Rector\PhpParser\Tests\Rector\CatchAndClosureUseNameRector;

use Rector\PhpParser\Rector\CatchAndClosureUseNameRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CatchAndClosureUseNameRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return CatchAndClosureUseNameRector::class;
    }
}
