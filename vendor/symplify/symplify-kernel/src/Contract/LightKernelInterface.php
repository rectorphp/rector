<?php

declare (strict_types=1);
namespace RectorPrefix20211102\Symplify\SymplifyKernel\Contract;

use RectorPrefix20211102\Psr\Container\ContainerInterface;
/**
 * @api
 */
interface LightKernelInterface
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs($configFiles) : \RectorPrefix20211102\Psr\Container\ContainerInterface;
    public function getContainer() : \RectorPrefix20211102\Psr\Container\ContainerInterface;
}
