<?php

declare (strict_types=1);
namespace RectorPrefix20210705\Symplify\VendorPatches\HttpKernel;

use RectorPrefix20210705\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210705\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20210705\Symplify\ComposerJsonManipulator\Bundle\ComposerJsonManipulatorBundle;
use RectorPrefix20210705\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle;
use RectorPrefix20210705\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class VendorPatchesKernel extends \RectorPrefix20210705\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel
{
    public function registerContainerConfiguration(\RectorPrefix20210705\Symfony\Component\Config\Loader\LoaderInterface $loader) : void
    {
        $loader->load(__DIR__ . '/../../config/config.php');
    }
    /**
     * @return BundleInterface[]
     */
    public function registerBundles() : iterable
    {
        return [new \RectorPrefix20210705\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle(), new \RectorPrefix20210705\Symplify\ComposerJsonManipulator\Bundle\ComposerJsonManipulatorBundle()];
    }
}
