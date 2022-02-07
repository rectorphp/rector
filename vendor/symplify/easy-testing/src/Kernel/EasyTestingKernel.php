<?php

declare (strict_types=1);
namespace RectorPrefix20220207\Symplify\EasyTesting\Kernel;

use RectorPrefix20220207\Psr\Container\ContainerInterface;
use RectorPrefix20220207\Symplify\EasyTesting\ValueObject\EasyTestingConfig;
use RectorPrefix20220207\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class EasyTestingKernel extends \RectorPrefix20220207\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles) : \RectorPrefix20220207\Psr\Container\ContainerInterface
    {
        $configFiles[] = \RectorPrefix20220207\Symplify\EasyTesting\ValueObject\EasyTestingConfig::FILE_PATH;
        return $this->create($configFiles);
    }
}
