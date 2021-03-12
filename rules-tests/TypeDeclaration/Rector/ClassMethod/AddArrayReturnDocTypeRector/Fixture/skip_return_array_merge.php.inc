<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

final class SkipReturnArrayMerge
{
    /**
     * @var array<string, SmartFileInfo[]>
     */
    private $resolvedConfigFileInfos = [];

    /**
     * @param SmartFileInfo[] $setFileInfos
     * @return SmartFileInfo[]
     */
    public function run(SmartFileInfo $configFileInfo): array
    {
        $hash = sha1($configFileInfo->getRealPath());

        if (isset($this->resolvedConfigFileInfos[$hash])) {
            return $this->resolvedConfigFileInfos[$hash];
        }

        return [];
    }
}
