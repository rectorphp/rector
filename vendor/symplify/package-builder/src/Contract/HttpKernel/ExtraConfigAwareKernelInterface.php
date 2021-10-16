<?php

declare (strict_types=1);
namespace RectorPrefix20211016\Symplify\PackageBuilder\Contract\HttpKernel;

use RectorPrefix20211016\Symfony\Component\HttpKernel\KernelInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
interface ExtraConfigAwareKernelInterface extends \RectorPrefix20211016\Symfony\Component\HttpKernel\KernelInterface
{
    /**
     * @param string[]|SmartFileInfo[] $configs
     */
    public function setConfigs($configs) : void;
}
