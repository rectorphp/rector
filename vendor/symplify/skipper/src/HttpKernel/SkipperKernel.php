<?php

declare (strict_types=1);
namespace RectorPrefix20210612\Symplify\Skipper\HttpKernel;

use RectorPrefix20210612\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210612\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20210612\Symplify\Skipper\Bundle\SkipperBundle;
use RectorPrefix20210612\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle;
use RectorPrefix20210612\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class SkipperKernel extends \RectorPrefix20210612\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    public function registerContainerConfiguration(\RectorPrefix20210612\Symfony\Component\Config\Loader\LoaderInterface $loader) : void
    {
        $loader->load(__DIR__ . '/../../config/config.php');
        parent::registerContainerConfiguration($loader);
    }
    /**
     * @return BundleInterface[]
     */
    public function registerBundles() : iterable
    {
        return [new \RectorPrefix20210612\Symplify\Skipper\Bundle\SkipperBundle(), new \RectorPrefix20210612\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle()];
    }
}
