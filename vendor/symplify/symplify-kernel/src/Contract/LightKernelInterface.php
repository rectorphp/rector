<?php

declare (strict_types=1);
namespace RectorPrefix20220520\Symplify\SymplifyKernel\Contract;

use RectorPrefix20220520\Psr\Container\ContainerInterface;
/**
 * @api
 */
interface LightKernelInterface
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20220520\Psr\Container\ContainerInterface;
    public function getContainer() : \RectorPrefix20220520\Psr\Container\ContainerInterface;
}
