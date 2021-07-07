<?php

declare (strict_types=1);
namespace RectorPrefix20210707\Symplify\VendorPatches\HttpKernel;

use RectorPrefix20210707\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210707\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20210707\Symplify\ComposerJsonManipulator\Bundle\ComposerJsonManipulatorBundle;
use RectorPrefix20210707\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle;
use RectorPrefix20210707\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class VendorPatchesKernel extends \RectorPrefix20210707\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    /**
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     */
    public function registerContainerConfiguration($loader) : void
    {
        $loader->load(__DIR__ . '/../../config/config.php');
    }
    /**
     * @return BundleInterface[]
     */
    public function registerBundles() : iterable
    {
        return [new \RectorPrefix20210707\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle(), new \RectorPrefix20210707\Symplify\ComposerJsonManipulator\Bundle\ComposerJsonManipulatorBundle()];
    }
}
