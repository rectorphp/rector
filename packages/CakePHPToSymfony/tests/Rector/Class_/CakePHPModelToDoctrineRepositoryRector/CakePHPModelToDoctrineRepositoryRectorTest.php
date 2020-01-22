<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Class_\CakePHPModelToDoctrineRepositoryRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPModelToDoctrineRepositoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file, string $expectedRepositoryFilePath, string $expectedRepositoryContentFile): void
    {
        $this->doTestFile($file);

        $this->assertFileExists($expectedRepositoryFilePath);
        $this->assertFileEquals($expectedRepositoryContentFile, $expectedRepositoryFilePath);
    }

    public function provideDataForTest(): Iterator
    {
        yield [
            __DIR__ . '/Fixture/find_first.inc',
            $this->getTempPath() . '/FindFirstRepository.php',
            __DIR__ . '/Source/ExpectedFindFirstRepository.php',
        ];

        yield [
            __DIR__ . '/Fixture/find_all.php.inc',
            $this->getTempPath() . '/FindAllRepository.php',
            __DIR__ . '/Source/ExpectedFindAllRepository.php',
        ];

        yield [
            __DIR__ . '/Fixture/find_threaded.php.inc',
            $this->getTempPath() . '/FindThreadedRepository.php',
            __DIR__ . '/Source/ExpectedFindThreadedRepository.php',
        ];

        yield [
            __DIR__ . '/Fixture/find_count.php.inc',
            $this->getTempPath() . '/FindCountRepository.php',
            __DIR__ . '/Source/ExpectedFindCountRepository.php',
        ];

        yield [
            __DIR__ . '/Fixture/find_list.php.inc',
            $this->getTempPath() . '/FindListRepository.php',
            __DIR__ . '/Source/ExpectedFindListRepository.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return CakePHPModelToDoctrineRepositoryRector::class;
    }
}
