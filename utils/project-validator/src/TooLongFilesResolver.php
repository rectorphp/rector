<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator;

use Symplify\SmartFileSystem\SmartFileInfo;

final class TooLongFilesResolver
{
    /**
     * In windows the max-path length is 260 chars. we give a bit room for the path up to the rector project
     * @var int
     */
    public const MAX_FILE_LENGTH = 200;

    /**
     * @param SmartFileInfo[] $fileInfos
     * @return SmartFileInfo[]
     */
    public function resolve(array $fileInfos): array
    {
        return array_filter($fileInfos, function (SmartFileInfo $fileInfo): bool {
            $filePathLength = strlen($fileInfo->getRealPath());
            return $filePathLength > self::MAX_FILE_LENGTH;
        });
    }
}
