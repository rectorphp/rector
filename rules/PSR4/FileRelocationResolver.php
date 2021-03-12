<?php

declare(strict_types=1);

namespace Rector\PSR4;

use Nette\Utils\Strings;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\PSR4\Tests\FileRelocationResolverTest
 */
final class FileRelocationResolver
{
    /**
     * @var string
     */
    private const NAMESPACE_SEPARATOR = '\\';

    /**
     * @param string[] $groupNames
     */
    public function createNewFileDestination(
        SmartFileInfo $smartFileInfo,
        string $suffixName,
        array $groupNames
    ): string {
        $newDirectory = $this->resolveRootDirectory($smartFileInfo, $suffixName, $groupNames);

        return $newDirectory . DIRECTORY_SEPARATOR . $smartFileInfo->getFilename();
    }

    /**
     * @param string[] $groupNames
     */
    public function resolveNewNamespaceName(Namespace_ $namespace, string $suffixName, array $groupNames): string
    {
        /** @var Name $name */
        $name = $namespace->name;
        $currentNamespaceParts = $name->parts;

        return $this->resolveNearestRootWithCategory(
            $currentNamespaceParts,
            $suffixName,
            self::NAMESPACE_SEPARATOR,
            $groupNames
        );
    }

    public function resolveNewFileLocationFromOldClassToNewClass(
        SmartFileInfo $smartFileInfo,
        string $oldClass,
        string $newClass
    ): string {
        $beforeToAfterPart = $this->resolveBeforeToAfterPartBetweenClassNames($oldClass, $newClass);

        return $this->replaceRelativeFilePathsWithBeforeAfter($smartFileInfo, $beforeToAfterPart);
    }

    /**
     * @param string[] $groupNames
     */
    private function resolveRootDirectory(SmartFileInfo $smartFileInfo, string $suffixName, array $groupNames): string
    {
        $currentTraversePath = dirname($smartFileInfo->getRelativeFilePath());
        $currentDirectoryParts = explode(DIRECTORY_SEPARATOR, $currentTraversePath);

        return $this->resolveNearestRootWithCategory(
            $currentDirectoryParts,
            $suffixName,
            DIRECTORY_SEPARATOR,
            $groupNames
        );
    }

    /**
     * @param string[] $groupNames
     * @param string[] $nameParts
     */
    private function resolveNearestRootWithCategory(
        array $nameParts,
        string $suffixName,
        string $separator,
        array $groupNames
    ): string {
        $reversedNameParts = array_reverse($nameParts);

        $removedParts = [];
        $hasStopped = false;

        foreach ($reversedNameParts as $key => $reversedNamePart) {
            unset($reversedNameParts[$key]);

            if (in_array($reversedNamePart, $groupNames, true)) {
                $hasStopped = true;
                break;
            }

            $removedParts[] = $reversedNamePart;
        }

        if (! $hasStopped) {
            $rootNameParts = $nameParts;
            $rootNameParts[] = $suffixName;
        } else {
            $rootNameParts = array_reverse($reversedNameParts);
            $rootNameParts[] = $suffixName;

            if ($removedParts !== []) {
                $rootNameParts = array_merge($rootNameParts, $removedParts);
            }
        }

        return implode($separator, $rootNameParts);
    }

    /**
     * @return string[]
     */
    private function resolveBeforeToAfterPartBetweenClassNames(string $oldClass, string $newClass): array
    {
        $oldClassNameParts = explode(self::NAMESPACE_SEPARATOR, $oldClass);
        $newClassNameParts = explode(self::NAMESPACE_SEPARATOR, $newClass);

        $beforeToAfterParts = [];
        foreach ($oldClassNameParts as $key => $oldClassNamePart) {
            if (! isset($newClassNameParts[$key])) {
                continue;
            }

            $newClassNamePart = $newClassNameParts[$key];
            if ($oldClassNamePart === $newClassNamePart) {
                continue;
            }

            $beforeToAfterParts[$oldClassNamePart] = $newClassNamePart;
        }

        return $beforeToAfterParts;
    }

    /**
     * @param string[] $beforeToAfterPart
     */
    private function replaceRelativeFilePathsWithBeforeAfter(
        SmartFileInfo $oldSmartFileInfo,
        array $beforeToAfterPart
    ): string {
        // A. first "dir has changed" dummy detection
        $relativeFilePathParts = Strings::split(
            $oldSmartFileInfo->getRelativeFilePath(),
            '#' . DIRECTORY_SEPARATOR . '#'
        );

        foreach ($relativeFilePathParts as $key => $relativeFilePathPart) {
            if (! isset($beforeToAfterPart[$relativeFilePathPart])) {
                continue;
            }

            $relativeFilePathParts[$key] = $beforeToAfterPart[$relativeFilePathPart];

            // clear from further use
            unset($beforeToAfterPart[$relativeFilePathPart]);
        }

        return implode(DIRECTORY_SEPARATOR, $relativeFilePathParts);
    }
}
