<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Bootstrap;

use Symplify\SmartFileSystem\SmartFileInfo;
final class BootstrapConfigs
{
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo|null
     */
    private $mainConfigFileInfo;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo[]
     */
    private $setConfigFileInfos;
    /**
     * @param SmartFileInfo[] $setConfigFileInfos
     */
    public function __construct(?\Symplify\SmartFileSystem\SmartFileInfo $mainConfigFileInfo, array $setConfigFileInfos)
    {
        $this->mainConfigFileInfo = $mainConfigFileInfo;
        $this->setConfigFileInfos = $setConfigFileInfos;
    }
    public function getMainConfigFileInfo() : ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->mainConfigFileInfo;
    }
    /**
     * @return SmartFileInfo[]
     */
    public function getConfigFileInfos() : array
    {
        $configFileInfos = [];
        if ($this->mainConfigFileInfo !== null) {
            $configFileInfos[] = $this->mainConfigFileInfo;
        }
        return \array_merge($configFileInfos, $this->setConfigFileInfos);
    }
}
