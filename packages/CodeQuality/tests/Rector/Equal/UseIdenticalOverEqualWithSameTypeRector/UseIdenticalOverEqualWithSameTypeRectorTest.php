<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;

use Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UseIdenticalOverEqualWithSameTypeRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/skip_objects.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return UseIdenticalOverEqualWithSameTypeRector::class;
    }
}
