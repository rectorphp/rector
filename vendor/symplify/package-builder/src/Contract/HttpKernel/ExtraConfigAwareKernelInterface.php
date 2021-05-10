<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\PackageBuilder\Contract\HttpKernel;

use RectorPrefix20210510\Symfony\Component\HttpKernel\KernelInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
interface ExtraConfigAwareKernelInterface extends KernelInterface
{
    /**
     * @param string[]|SmartFileInfo[] $configs
     */
    public function setConfigs(array $configs) : void;
}
