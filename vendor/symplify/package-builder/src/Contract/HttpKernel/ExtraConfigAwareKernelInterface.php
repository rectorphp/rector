<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Symplify\PackageBuilder\Contract\HttpKernel;

use RectorPrefix20211020\Symfony\Component\HttpKernel\KernelInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
interface ExtraConfigAwareKernelInterface extends \RectorPrefix20211020\Symfony\Component\HttpKernel\KernelInterface
{
    /**
     * @param string[]|SmartFileInfo[] $configs
     */
    public function setConfigs($configs) : void;
}
