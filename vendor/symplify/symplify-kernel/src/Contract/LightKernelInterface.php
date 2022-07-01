<?php

declare (strict_types=1);
namespace RectorPrefix202207\Symplify\SymplifyKernel\Contract;

use RectorPrefix202207\Psr\Container\ContainerInterface;
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
