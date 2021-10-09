<?php

declare (strict_types=1);
namespace RectorPrefix20211009\Symplify\EasyTesting\HttpKernel;

use RectorPrefix20211009\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20211009\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class EasyTestingKernel extends \RectorPrefix20211009\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    /**
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     */
    public function registerContainerConfiguration($loader) : void
    {
        $loader->load(__DIR__ . '/../../config/config.php');
    }
}
