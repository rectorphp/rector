<?php

declare (strict_types=1);
namespace RectorPrefix20220527\Symplify\SymplifyKernel\Contract;

use RectorPrefix20220527\Psr\Container\ContainerInterface;
/**
 * @api
 */
interface LightKernelInterface
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : ContainerInterface;
    public function getContainer() : ContainerInterface;
}
