<?php

declare (strict_types=1);
namespace RectorPrefix20210608\Symplify\PackageBuilder\Contract\HttpKernel;

use RectorPrefix20210608\Symfony\Component\HttpKernel\KernelInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
interface ExtraConfigAwareKernelInterface extends \RectorPrefix20210608\Symfony\Component\HttpKernel\KernelInterface
{
    /**
     * @param string[]|SmartFileInfo[] $configs
     */
    public function setConfigs(array $configs) : void;
}
