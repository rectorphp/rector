<?php declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector;

use Iterator;
use Nette\Utils\FileSystem;
use Rector\NetteTesterToPHPUnit\Rector\Class_\NetteTesterClassToPHPUnitClassRector;
use Rector\NetteTesterToPHPUnit\Rector\StaticCall\NetteAssertToPHPUnitAssertRector;
use Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\Source\NetteTesterTestCase;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NetteTesterPHPUnitRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        // prepare dummy data
        FileSystem::copy(__DIR__ . '/Copy', $this->getTempPath());

        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/test_class.php.inc'];
        yield [__DIR__ . '/Fixture/assert_true.php.inc'];
        yield [__DIR__ . '/Fixture/assert_type.php.inc'];
        yield [__DIR__ . '/Fixture/various_asserts.php.inc'];
        yield [__DIR__ . '/Fixture/kdyby_tests_events.php.inc'];
        yield [__DIR__ . '/Fixture/data_provider.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NetteAssertToPHPUnitAssertRector::class => [],
            NetteTesterClassToPHPUnitClassRector::class => [
                '$netteTesterTestCaseClass' => NetteTesterTestCase::class,
            ],
        ];
    }
}
