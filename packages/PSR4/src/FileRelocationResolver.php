<?php declare(strict_types=1);

namespace Rector\PSR4;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

/**
 * Will be used later
 */
final class FileRelocationResolver
{
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

        return $this->resolveNearestRootWithCategory($currentNamespaceParts, $suffixName, '\\', $groupNames);
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

        if ($hasStopped === false) {
            $rootNameParts = $nameParts;
            $rootNameParts[] = $suffixName;
        } else {
            $rootNameParts = array_reverse($reversedNameParts);
            $rootNameParts[] = $suffixName;

            if ($removedParts) {
                $rootNameParts = array_merge($rootNameParts, $removedParts);
            }
        }

        return implode($separator, $rootNameParts);
    }
}
