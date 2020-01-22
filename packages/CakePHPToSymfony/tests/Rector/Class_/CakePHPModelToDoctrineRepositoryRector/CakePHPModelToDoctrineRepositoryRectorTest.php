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
    public function test(string $file): void
    {
        $this->doTestFile($file);

        $repositoryFilePath = $this->getTempPath() . '/ActivityRepository.php';
        $this->assertFileExists($repositoryFilePath);
        $this->assertFileEquals(__DIR__ . '/Source/ExpectedActivityRepository.php', $repositoryFilePath);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return CakePHPModelToDoctrineRepositoryRector::class;
    }
}
