<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Bootstrap;

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
    private $setConfigFileInfos;

    /**
     * @param SmartFileInfo[] $setConfigFileInfos
     */
    public function __construct(?SmartFileInfo $mainConfigFileInfo, array $setConfigFileInfos)
    {
        $this->mainConfigFileInfo = $mainConfigFileInfo;
        $this->setConfigFileInfos = $setConfigFileInfos;
    }

    public function getMainConfigFileInfo(): ?SmartFileInfo
    {
        return $this->mainConfigFileInfo;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getConfigFileInfos(): array
    {
        $configFileInfos = [];
        if ($this->mainConfigFileInfo) {
            $configFileInfos[] = $this->mainConfigFileInfo;
        }

        $configFileInfos = array_merge($configFileInfos, $this->setConfigFileInfos);
        return $configFileInfos;
    }
}
