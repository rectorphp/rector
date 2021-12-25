<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

use Rector\Caching\UnchangedFilesFilter;
use Rector\Core\Util\StringUtils;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PhpFilesFinder
{
    public function __construct(
        private readonly FilesFinder $filesFinder,
        private readonly UnchangedFilesFilter $unchangedFilesFilter
    ) {
    }

    /**
     * @param string[] $paths
     * @return SmartFileInfo[]
     */
    public function findInPaths(array $paths): array
    {
        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles($paths);
        $suffixRegexPattern = StaticNonPhpFileSuffixes::getSuffixRegexPattern();

        // filter out non-PHP files
        foreach ($phpFileInfos as $key => $phpFileInfo) {
            $pathName = $phpFileInfo->getPathname();

            /**
             *  check .blade.php early so next .php check in next if can be skipped
             */
            if (str_ends_with($pathName, '.blade.php')) {
                unset($phpFileInfos[$key]);
                continue;
            }

            /**
             * obvious
             */
            if (str_ends_with($pathName, '.php')) {
                continue;
            }

            /**
             * only check with regex when needed
             */
            if (StringUtils::isMatch($pathName, $suffixRegexPattern)) {
                unset($phpFileInfos[$key]);
            }
        }

        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }
}
