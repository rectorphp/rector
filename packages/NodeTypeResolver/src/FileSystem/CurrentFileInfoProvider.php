<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\FileSystem;

use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class CurrentFileInfoProvider
{
    /**
     * @var SmartFileInfo|null
     */
    private $smartFileInfo;

    public function setCurrentFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $this->smartFileInfo = $smartFileInfo;
    }

    public function getSmartFileInfo(): ?SmartFileInfo
    {
        return $this->smartFileInfo;
    }
}
