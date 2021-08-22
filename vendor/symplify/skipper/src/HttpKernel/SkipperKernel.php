<?php

declare (strict_types=1);
namespace RectorPrefix20210822\Symplify\Skipper\HttpKernel;

use RectorPrefix20210822\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210822\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20210822\Symplify\Skipper\Bundle\SkipperBundle;
use RectorPrefix20210822\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle;
use RectorPrefix20210822\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class SkipperKernel extends \RectorPrefix20210822\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    /**
     * @param \RectorPrefix20210822\Symfony\Component\Config\Loader\LoaderInterface $loader
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
        return [new \RectorPrefix20210822\Symplify\Skipper\Bundle\SkipperBundle(), new \RectorPrefix20210822\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle()];
    }
}
