<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\SymplifyKernel\HttpKernel;

use RectorPrefix20210827\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210827\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20210827\Symfony\Component\HttpKernel\Kernel;
use RectorPrefix20210827\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210827\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle;
use RectorPrefix20210827\Symplify\SymplifyKernel\Strings\KernelUniqueHasher;
abstract class AbstractSymplifyKernel extends \RectorPrefix20210827\Symfony\Component\HttpKernel\Kernel implements \RectorPrefix20210827\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface
{
    /**
     * @var string[]
     */
    private $configs = [];
    public function getCacheDir() : string
    {
        return \sys_get_temp_dir() . '/' . $this->getUniqueKernelHash();
    }
    public function getLogDir() : string
    {
        return \sys_get_temp_dir() . '/' . $this->getUniqueKernelHash() . '_log';
    }
    /**
     * @return BundleInterface[]
     */
    public function registerBundles() : iterable
    {
        return [new \RectorPrefix20210827\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle()];
    }
    /**
     * @param string[]|SmartFileInfo[] $configs
     */
    public function setConfigs($configs) : void
    {
        foreach ($configs as $config) {
            if ($config instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
                $config = $config->getRealPath();
            }
            $this->configs[] = $config;
        }
    }
    /**
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     */
    public function registerContainerConfiguration($loader) : void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
    private function getUniqueKernelHash() : string
    {
        $kernelUniqueHasher = new \RectorPrefix20210827\Symplify\SymplifyKernel\Strings\KernelUniqueHasher();
        return $kernelUniqueHasher->hashKernelClass(static::class);
    }
}
