<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyArraySearchRector;

use Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SimplifyArraySearchRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SimplifyArraySearchRector::class;
    }
}
