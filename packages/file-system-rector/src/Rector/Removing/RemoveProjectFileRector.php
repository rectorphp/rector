<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Rector\Removing;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Configuration\Option;
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
        return new RectorDefinition('Remove file relative to project directory', [
            new CodeSample(
                <<<'CODE_SAMPLE'
// someFile/ToBeRemoved.txt
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
CODE_SAMPLE
            ),
        ]);
    }

    public function configure(array $configuration): void
    {
        $this->filePathsToRemove = $configuration[self::FILE_PATHS_TO_REMOVE] ?? [];
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
            'Complete "parameters > %s" in rector.php, so Rector can found your directory',
            Option::PROJECT_DIRECTORY_PARAMETER
        ));
    }
}
