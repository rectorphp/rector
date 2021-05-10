<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SymplifyKernel\HttpKernel;

use RectorPrefix20210510\Symfony\Component\Config\Loader\LoaderInterface;
use RectorPrefix20210510\Symfony\Component\HttpKernel\Bundle\BundleInterface;
use RectorPrefix20210510\Symfony\Component\HttpKernel\Kernel;
use RectorPrefix20210510\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210510\Symplify\SymplifyKernel\Bundle\SymplifyKernelBundle;
use RectorPrefix20210510\Symplify\SymplifyKernel\Strings\KernelUniqueHasher;
abstract class AbstractSymplifyKernel extends Kernel implements ExtraConfigAwareKernelInterface
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
        return [new SymplifyKernelBundle()];
    }
    /**
     * @param string[]|SmartFileInfo[] $configs
     */
    public function setConfigs(array $configs) : void
    {
        foreach ($configs as $config) {
            if ($config instanceof SmartFileInfo) {
                $config = $config->getRealPath();
            }
            $this->configs[] = $config;
        }
    }
    public function registerContainerConfiguration(LoaderInterface $loader) : void
    {
        foreach ($this->configs as $config) {
            $loader->load($config);
        }
    }
    private function getUniqueKernelHash() : string
    {
        $kernelUniqueHasher = new KernelUniqueHasher();
        return $kernelUniqueHasher->hashKernelClass(static::class);
    }
}
