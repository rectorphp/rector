<?php declare(strict_types=1);

namespace Rector\PhpParser\Tests\Rector\SetLineRector;

use Rector\PhpParser\Rector\SetLineRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SetLineRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SetLineRector::class;
    }
}
