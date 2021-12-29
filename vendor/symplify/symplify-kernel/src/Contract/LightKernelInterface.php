<?php

declare (strict_types=1);
namespace RectorPrefix20211229\Symplify\SymplifyKernel\Contract;

use RectorPrefix20211229\Psr\Container\ContainerInterface;
/**
 * @api
 */
interface LightKernelInterface
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20211229\Psr\Container\ContainerInterface;
    public function getContainer() : \RectorPrefix20211229\Psr\Container\ContainerInterface;
}
