<?php

declare(strict_types=1);

namespace Rector\Autodiscovery\Tests\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;

use Iterator;
use Rector\Autodiscovery\Rector\FileSystem\MoveServicesBySuffixToDirectoryRector;
use Rector\Autodiscovery\Tests\Rector\FileSystem\MoveInterfacesToContractNamespaceDirectoryRector\ValueObject\InputFilePathWithExpectedFilePathAndContent;
use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MutualRenameTest extends AbstractFileSystemRectorTestCase
{
    /**
     * @dataProvider provideData()
     *
     * @param InputFilePathWithExpectedFilePathAndContent[] $extraFiles
     */
    public function test(
        SmartFileInfo $originalFileInfo,
        string $expectedFileLocation,
        string $expectedFileContent,
        array $extraFiles = []
    ): void {
        //$extraFilePaths = array_keys($extraFiles);
        $this->doTestFileInfo($originalFileInfo, $extraFiles);

        $this->assertFileExists($expectedFileLocation);
        $this->assertFileEquals($expectedFileContent, $expectedFileLocation);

        $this->doTestExtraFileInfos($extraFiles);
    }

    public function provideData(): Iterator
    {
        yield [
            new SmartFileInfo(__DIR__ . '/SourceMutualRename/Controller/Nested/AbstractBaseWithSpaceMapper.php'),
            $this->getFixtureTempDirectory() . '/SourceMutualRename/Mapper/Nested/AbstractBaseWithSpaceMapper.php',
            __DIR__ . '/ExpectedMutualRename/Mapper/Nested/AbstractBaseWithSpaceMapper.php.inc',

            // extra files
            [
                new InputFilePathWithExpectedFilePathAndContent(
                __DIR__ . '/SourceMutualRename/Entity/UserWithSpaceMapper.php',
                    $this->getFixtureTempDirectory() . '/SourceMutualRename/Mapper/UserWithSpaceMapper.php',
                    __DIR__ . '/ExpectedMutualRename/Mapper/UserWithSpaceMapper.php.inc'
                ),
            ],
        ];

        // inversed order, but should have the same effect
        yield [
            new SmartFileInfo(__DIR__ . '/SourceMutualRename/Entity/UserMapper.php'),
            $this->getFixtureTempDirectory() . '/SourceMutualRename/Mapper/UserMapper.php',
            __DIR__ . '/ExpectedMutualRename/Mapper/UserMapper.php.inc',

            // extra files
            [
                new InputFilePathWithExpectedFilePathAndContent(
                __DIR__ . '/SourceMutualRename/Controller/Nested/AbstractBaseMapper.php',
                    $this->getFixtureTempDirectory() . '/SourceMutualRename/Mapper/Nested/AbstractBaseMapper.php',
                     __DIR__ . '/ExpectedMutualRename/Mapper/Nested/AbstractBaseMapper.php.inc'
                ),

                // includes NEON/YAML file renames
                new InputFilePathWithExpectedFilePathAndContent(
                    __DIR__ . '/SourceMutualRename/config/some_config.neon',
                     $this->getFixtureTempDirectory() . '/SourceMutualRename/config/some_config.neon',
                     __DIR__ . '/ExpectedMutualRename/config/expected_some_config.neon'
                ),
            ],
        ];
    }

    /**
     * @return array<string, array<int, string[]>>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MoveServicesBySuffixToDirectoryRector::class => [
                MoveServicesBySuffixToDirectoryRector::GROUP_NAMES_BY_SUFFIX => ['Mapper'],
            ],
        ];
    }
}
