<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\Skipper\HttpKernel;

use RectorPrefix20210510\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210510\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20210510\Symplify\Skipper\Bundle\SkipperBundle;
use RectorPrefix20210510\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle;
use RectorPrefix20210510\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class SkipperKernel extends AbstractSymplifyKernel
{
    public function registerContainerConfiguration(LoaderInterface $loader) : void
    {
        $loader->load(__DIR__ . '/../../config/config.php');
        parent::registerContainerConfiguration($loader);
    }
    /**
     * @return BundleInterface[]
     */
    public function registerBundles() : iterable
    {
        return [new SkipperBundle(), new SymplifyKernelBundle()];
    }
}
