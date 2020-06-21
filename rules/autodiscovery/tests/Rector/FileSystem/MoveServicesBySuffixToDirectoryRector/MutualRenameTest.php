<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;

final class MutualRenameTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideData()
     *
     * @param string[][] $extraFiles
     */
    public function test(
        string $originalFile,
        string $expectedFileLocation,
        string $expectedFileContent,
        array $extraFiles = []
    ): void {
        $this->doTestFile($originalFile, array_keys($extraFiles));

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileContent, $expectedFileLocation);

        foreach ($extraFiles as $extraFile) {
            $this->assertFileExists($extraFile['location']);
            $this->assertFileEquals($extraFile['content'], $extraFile['location']);
        }
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/SourceMutualRename/Controller/Nested/AbstractBaseWithSpaceMapper.php',
            $this->getFixtureTempDirectory() . '/SourceMutualRename/Mapper/Nested/AbstractBaseWithSpaceMapper.php',
            __DIR__ . '/ExpectedMutualRename/Mapper/Nested/AbstractBaseWithSpaceMapper.php.inc',

            // extra files
            [
                __DIR__ . '/SourceMutualRename/Entity/UserWithSpaceMapper.php' => [
                    'location' => $this->getFixtureTempDirectory() . '/SourceMutualRename/Mapper/UserWithSpaceMapper.php',
                    'content' => __DIR__ . '/ExpectedMutualRename/Mapper/UserWithSpaceMapper.php.inc',
                ],
            ],
        ];

        // inversed order, but should have the same effect
        yield [
            __DIR__ . '/SourceMutualRename/Entity/UserMapper.php',
            $this->getFixtureTempDirectory() . '/SourceMutualRename/Mapper/UserMapper.php',
            __DIR__ . '/ExpectedMutualRename/Mapper/UserMapper.php.inc',

            // extra files
            [
                __DIR__ . '/SourceMutualRename/Controller/Nested/AbstractBaseMapper.php' => [
                    'location' => $this->getFixtureTempDirectory() . '/SourceMutualRename/Mapper/Nested/AbstractBaseMapper.php',
                    'content' => __DIR__ . '/ExpectedMutualRename/Mapper/Nested/AbstractBaseMapper.php.inc',
                ],
            ],
        ];
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            MoveServicesBySuffixToDirectoryRector::class => [
                '$groupNamesBySuffix' => ['Mapper'],
            ],
        ];
    }
}
