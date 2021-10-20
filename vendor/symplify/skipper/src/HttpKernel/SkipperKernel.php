<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Symplify\Skipper\HttpKernel;

use RectorPrefix20211020\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20211020\Symplify\Skipper\Bundle\SkipperBundle;
use RectorPrefix20211020\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle;
use RectorPrefix20211020\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class SkipperKernel extends \RectorPrefix20211020\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    /**
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     */
    public function registerContainerConfiguration($loader) : void
    {
        $loader->load(__DIR__ . '/../../config/config.php');
        parent::registerContainerConfiguration($loader);
    }
    /**
     * @return BundleInterface[]
     */
    public function registerBundles() : iterable
    {
        return [new \RectorPrefix20211020\Symplify\Skipper\Bundle\SkipperBundle(), new \RectorPrefix20211020\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle()];
    }
}
