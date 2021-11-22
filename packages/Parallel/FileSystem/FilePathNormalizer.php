<?php

declare(strict_types=1);

namespace Rector\Parallel\FileSystem;

use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @api @todo complete parallel run
 */
final class FilePathNormalizer
{
    /**
     * @param SmartFileInfo[] $fileInfos
     * @return string[]
     */
    public function resolveFilePathsFromFileInfos(array $fileInfos): array
    {
        $filePaths = [];
        foreach ($fileInfos as $fileInfo) {
            $filePaths[] = $fileInfo->getRelativeFilePathFromCwd();
        }

        return $filePaths;
    }
}
