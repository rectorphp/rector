<?php declare(strict_types=1);

namespace Rector\Architecture\Tests\Rector\Class_\ConstructorInjectionToActionInjectionRector;

use Iterator;
use Rector\Architecture\Rector\Class_\ConstructorInjectionToActionInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ConstructorInjectionToActionInjectionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/duplicate.php.inc'];
        yield [__DIR__ . '/Fixture/skip_scalars.php.inc'];
        yield [__DIR__ . '/Fixture/skip_non_action_methods.php.inc'];
        yield [__DIR__ . '/Fixture/manage_different_naming.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ConstructorInjectionToActionInjectionRector::class;
    }
}
