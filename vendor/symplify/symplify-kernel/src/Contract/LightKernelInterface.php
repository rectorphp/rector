<?php

declare (strict_types=1);
namespace RectorPrefix20220514\Symplify\SymplifyKernel\Contract;

use RectorPrefix20220514\Psr\Container\ContainerInterface;
/**
 * @api
 */
interface LightKernelInterface
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20220514\Psr\Container\ContainerInterface;
    public function getContainer() : \RectorPrefix20220514\Psr\Container\ContainerInterface;
}
