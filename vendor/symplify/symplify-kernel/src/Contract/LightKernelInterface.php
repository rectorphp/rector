<?php

declare (strict_types=1);
namespace RectorPrefix20211117\Symplify\SymplifyKernel\Contract;

use RectorPrefix20211117\Psr\Container\ContainerInterface;
/**
 * @api
 */
interface LightKernelInterface
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20211117\Psr\Container\ContainerInterface;
    public function getContainer() : \RectorPrefix20211117\Psr\Container\ContainerInterface;
}
