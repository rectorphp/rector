<?php

declare (strict_types=1);
namespace Rector\Core\Kernel;

use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix202306\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix202306\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix202306\Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use RectorPrefix202306\Symfony\Component\Filesystem\Filesystem;
/**
 * see https://symfony.com/doc/current/components/dependency_injection/compilation.html#dumping-the-configuration-for-performance
 */
final class CachedContainerBuilder
{
    /**
     * @var string
     */
    private $cacheDir;
    /**
     * @var string
     */
    private $cacheKey;
    public function __construct(string $cacheDir, string $cacheKey)
    {
        $this->cacheDir = $cacheDir;
        $this->cacheKey = $cacheKey;
        if (\substr_compare($cacheDir, '/', -\strlen('/')) !== 0) {
            throw new ShouldNotHappenException(\sprintf('Cache dir "%s" must end with "/"', $cacheDir));
        }
    }
    /**
     * @param string[] $configFiles
     * @param callable(string[] $configFiles):ContainerBuilder $containerBuilderCallback
     */
    public function build(array $configFiles, string $hash, callable $containerBuilderCallback) : ContainerInterface
    {
        $filesystem = new Filesystem();
        $className = 'RectorKernel' . $hash;
        $file = $this->cacheDir . 'kernel-' . $this->cacheKey . '-' . $hash . '.php';
        if (\file_exists($file)) {
            require_once $file;
            $className = '\\' . __NAMESPACE__ . '\\' . $className;
            $cachedContainer = new $className();
            if (!$cachedContainer instanceof ContainerInterface) {
                throw new ShouldNotHappenException();
            }
            $container = new \Rector\Core\Kernel\CacheInvalidatingContainer($cachedContainer);
        } else {
            $container = $containerBuilderCallback($configFiles);
            $phpDumper = new PhpDumper($container);
            $dumpedContainer = $phpDumper->dump(['class' => $className, 'namespace' => __NAMESPACE__]);
            if (!\is_string($dumpedContainer)) {
                throw new ShouldNotHappenException();
            }
            $filesystem->dumpFile($file, $dumpedContainer);
        }
        return $container;
    }
    public function clearCache() : void
    {
        if (!\is_writable($this->cacheDir)) {
            return;
        }
        $cacheFiles = \glob($this->cacheDir . 'kernel-*.php');
        if ($cacheFiles === \false) {
            return;
        }
        $filesystem = new Filesystem();
        $filesystem->remove($cacheFiles);
    }
}
