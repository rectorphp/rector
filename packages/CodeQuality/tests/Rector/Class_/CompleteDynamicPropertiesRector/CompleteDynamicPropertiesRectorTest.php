<?php declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector;

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CompleteDynamicPropertiesRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/multiple_types.php.inc',
            __DIR__ . '/Fixture/skip_defined.php.inc',
            __DIR__ . '/Fixture/skip_parent_property.php.inc',
            __DIR__ . '/Fixture/skip_trait_used.php.inc',
            __DIR__ . '/Fixture/skip_magic_parent.php.inc',
            __DIR__ . '/Fixture/skip_magic.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return CompleteDynamicPropertiesRector::class;
    }
}
