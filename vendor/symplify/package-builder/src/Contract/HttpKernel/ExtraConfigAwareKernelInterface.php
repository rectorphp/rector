<?php

declare (strict_types=1);
namespace RectorPrefix20211006\Symplify\PackageBuilder\Contract\HttpKernel;

use RectorPrefix20211006\Symfony\Component\HttpKernel\KernelInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
interface ExtraConfigAwareKernelInterface extends \RectorPrefix20211006\Symfony\Component\HttpKernel\KernelInterface
{
    /**
     * @param string[]|SmartFileInfo[] $configs
     */
    public function setConfigs($configs) : void;
}
