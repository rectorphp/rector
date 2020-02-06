<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Class_\CakePHPModelToDoctrineRepositoryRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPModelToDoctrineRepositoryRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file, string $expectedRepositoryFilePath, string $expectedRepositoryContentFile): void
    {
        $this->doTestFile($file);

        $this->assertFileExists($expectedRepositoryFilePath);
        $this->assertFileEquals($expectedRepositoryContentFile, $expectedRepositoryFilePath);
    }

    public function provideData(): Iterator
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

//        WIP
//        yield [
//            __DIR__ . '/Fixture/find_list_with_one_argument.php.inc',
//            $this->getTempPath() . '/FindListWithOneArgumentRepository.php',
//            __DIR__ . '/Source/ExpectedFindListWithOneArgumentRepository.php',
//        ];
    }

    protected function getRectorClass(): string
    {
        return CakePHPModelToDoctrineRepositoryRector::class;
    }
}
