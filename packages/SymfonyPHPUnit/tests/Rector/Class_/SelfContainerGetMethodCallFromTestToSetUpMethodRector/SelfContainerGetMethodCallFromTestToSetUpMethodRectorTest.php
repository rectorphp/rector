<?php declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector;

use Rector\SymfonyPHPUnit\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector;
use Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source\DummyKernelTestCase;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SelfContainerGetMethodCallFromTestToSetUpMethodRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/existing_setup.php.inc'];
        yield [__DIR__ . '/Fixture/string_service_name.php.inc'];
        yield [__DIR__ . '/Fixture/extends_parent_class_with_property.php.inc'];
        yield [__DIR__ . '/Fixture/instant_call.php.inc'];
        yield [__DIR__ . '/Fixture/skip_sessions.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            SelfContainerGetMethodCallFromTestToSetUpMethodRector::class => [
                '$kernelTestCaseClass' => DummyKernelTestCase::class,
            ],
        ];
    }
}
