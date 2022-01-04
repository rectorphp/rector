<?php

declare (strict_types=1);
namespace RectorPrefix20220104\Symplify\SymplifyKernel\Contract;

use RectorPrefix20220104\Psr\Container\ContainerInterface;
/**
 * @api
 */
interface LightKernelInterface
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20220104\Psr\Container\ContainerInterface;
    public function getContainer() : \RectorPrefix20220104\Psr\Container\ContainerInterface;
}
