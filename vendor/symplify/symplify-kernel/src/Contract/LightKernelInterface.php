<?php

declare (strict_types=1);
namespace RectorPrefix20220305\Symplify\SymplifyKernel\Contract;

use RectorPrefix20220305\Psr\Container\ContainerInterface;
/**
 * @api
 */
interface LightKernelInterface
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20220305\Psr\Container\ContainerInterface;
    public function getContainer() : \RectorPrefix20220305\Psr\Container\ContainerInterface;
}
