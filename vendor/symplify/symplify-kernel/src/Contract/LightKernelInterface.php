<?php

declare (strict_types=1);
namespace RectorPrefix20211110\Symplify\SymplifyKernel\Contract;

use RectorPrefix20211110\Psr\Container\ContainerInterface;
/**
 * @api
 */
interface LightKernelInterface
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20211110\Psr\Container\ContainerInterface;
    public function getContainer() : \RectorPrefix20211110\Psr\Container\ContainerInterface;
}
