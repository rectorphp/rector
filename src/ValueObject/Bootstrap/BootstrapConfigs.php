<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject\Bootstrap;

use Symplify\SmartFileSystem\SmartFileInfo;

final class BootstrapConfigs
{
    /**
     * @param SmartFileInfo[] $setConfigFileInfos
     */
    public function __construct(
        private ?SmartFileInfo $mainConfigFileInfo,
        private array $setConfigFileInfos
    ) {
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
        if ($this->mainConfigFileInfo !== null) {
            $configFileInfos[] = $this->mainConfigFileInfo;
        }
        return array_merge($configFileInfos, $this->setConfigFileInfos);
    }
}
