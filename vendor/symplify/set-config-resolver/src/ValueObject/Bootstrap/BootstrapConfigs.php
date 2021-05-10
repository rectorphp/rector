<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SetConfigResolver\ValueObject\Bootstrap;

use Symplify\SmartFileSystem\SmartFileInfo;
final class BootstrapConfigs
{
    /**
     * @var SmartFileInfo|null
     */
    private $mainConfigFileInfo;
    /**
     * @var SmartFileInfo[]
     */
    private $setConfigFileInfos = [];
    /**
     * @param SmartFileInfo[] $setConfigFileInfos
     */
    public function __construct(?SmartFileInfo $mainConfigFileInfo, array $setConfigFileInfos)
    {
        $this->mainConfigFileInfo = $mainConfigFileInfo;
        $this->setConfigFileInfos = $setConfigFileInfos;
    }
    public function getMainConfigFileInfo() : ?SmartFileInfo
    {
        return $this->mainConfigFileInfo;
    }
    /**
     * @return SmartFileInfo[]
     */
    public function getConfigFileInfos() : array
    {
        if (!$this->mainConfigFileInfo instanceof SmartFileInfo) {
            return $this->setConfigFileInfos;
        }
        return \array_merge($this->setConfigFileInfos, [$this->mainConfigFileInfo]);
    }
}
