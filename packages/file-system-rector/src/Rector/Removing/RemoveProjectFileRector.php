<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Rector\Removing;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveProjectFileRector extends AbstractFileSystemRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const FILE_PATHS_TO_REMOVE = '$filePathsToRemove';

    /**
     * @var string[]
     */
    private $filePathsToRemove = [];

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        if ($this->filePathsToRemove === []) {
            return;
        }

        $projectDirectory = getcwd();

        $relativePathInProject = $smartFileInfo->getRelativeFilePathFromDirectory($projectDirectory);

        foreach ($this->filePathsToRemove as $filePathsToRemove) {
            if ($relativePathInProject !== $filePathsToRemove) {
                continue;
            }

            $this->removeFile($smartFileInfo);
        }
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove file relative to project directory', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
// someFile/ToBeRemoved.txt
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
CODE_SAMPLE
                ,
                [
                    self::FILE_PATHS_TO_REMOVE => ['someFile/ToBeRemoved.txt'],
                ]
            ),
        ]);
    }

    public function configure(array $configuration): void
    {
        $this->filePathsToRemove = $configuration[self::FILE_PATHS_TO_REMOVE] ?? [];
    }
}
