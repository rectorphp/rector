<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Rector\Removing;

use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Configuration\Option;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\FileSystemRector\Tests\Rector\Removing\RemoveProjectFileRector\RemoveProjectFileRectorTest
 */
final class RemoveProjectFileRector extends AbstractFileSystemRector
{
    /**
     * @var string[]
     */
    private $filePathsToRemove = [];

    /**
     * @param string[] $filePathsToRemove
     */
    public function __construct(array $filePathsToRemove = [])
    {
        $this->filePathsToRemove = $filePathsToRemove;
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        if ($this->filePathsToRemove === []) {
            return;
        }

        $projectDirectory = $this->getProjectDirectory();

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
        return new RectorDefinition('Remove file relative to project directory');
    }

    private function getProjectDirectory(): string
    {
        $this->ensureProjectDirectoryIsSet();

        return (string) $this->parameterProvider->provideParameter(Option::PROJECT_DIRECTORY_PARAMETER);
    }

    private function ensureProjectDirectoryIsSet(): void
    {
        $projectDirectory = $this->parameterProvider->provideParameter(Option::PROJECT_DIRECTORY_PARAMETER);

        // has value? → skip
        if ($projectDirectory) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Complete "parameters > %s" in rector.yaml, so Rector can found your directory',
            Option::PROJECT_DIRECTORY_PARAMETER
        ));
    }
}
