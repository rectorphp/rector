<?php

declare (strict_types=1);
namespace Rector\Console\Rule;

use RectorPrefix202610\Composer\Autoload\ClassLoader;
use Rector\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use ReflectionClass;
use SplFileInfo;
use RectorPrefix202610\Symfony\Component\Finder\Finder;
/**
 * Discovers all Rector rule classes available on disk - in core and rector-* packages -
 * regardless of the current config, by scanning the registered PSR-4 prefixes.
 *
 * @see \Rector\Tests\Console\Rule\AllRectorRulesProviderTest
 */
final class AllRectorRulesProvider
{
    /**
     * @return array<class-string<RectorInterface>>
     */
    public function provide(): array
    {
        $rectorClasses = [];
        foreach ($this->resolvePsr4Prefixes() as $namespacePrefix => $directories) {
            if (strncmp($namespacePrefix, 'Rector\\', strlen('Rector\\')) !== 0) {
                continue;
            }
            foreach ($directories as $directory) {
                if (!is_dir($directory)) {
                    continue;
                }
                foreach ($this->findRectorFiles($directory) as $fileInfo) {
                    $className = $this->resolveClassName($namespacePrefix, $directory, $fileInfo);
                    if ($this->isConcreteRectorRule($className)) {
                        $rectorClasses[] = $className;
                    }
                }
            }
        }
        $rectorClasses = array_unique($rectorClasses);
        sort($rectorClasses);
        return $rectorClasses;
    }
    /**
     * @return array<string, string[]>
     */
    private function resolvePsr4Prefixes(): array
    {
        $psr4Prefixes = [];
        // there can be multiple registered class loaders; merge their prefixes
        foreach (spl_autoload_functions() as $autoloadFunction) {
            if (!is_array($autoloadFunction) || !$autoloadFunction[0] instanceof ClassLoader) {
                continue;
            }
            foreach ($autoloadFunction[0]->getPrefixesPsr4() as $namespacePrefix => $directories) {
                $psr4Prefixes[$namespacePrefix] = array_merge($psr4Prefixes[$namespacePrefix] ?? [], $directories);
            }
        }
        return $psr4Prefixes;
    }
    /**
     * @return iterable<SplFileInfo>
     */
    private function findRectorFiles(string $directory): iterable
    {
        return Finder::create()->files()->name('*Rector.php')->in($directory);
    }
    private function resolveClassName(string $namespacePrefix, string $directory, SplFileInfo $fileInfo): string
    {
        $relativePath = (string) substr($fileInfo->getPathname(), strlen($directory) + 1, -strlen('.php'));
        return $namespacePrefix . str_replace('/', '\\', $relativePath);
    }
    /**
     * @phpstan-assert-if-true class-string<RectorInterface> $className
     */
    private function isConcreteRectorRule(string $className): bool
    {
        // is_a() autoloads the class and returns false when it does not exist
        if (!is_a($className, RectorInterface::class, \true)) {
            return \false;
        }
        if (is_a($className, PostRectorInterface::class, \true)) {
            return \false;
        }
        $reflectionClass = new ReflectionClass($className);
        return !$reflectionClass->isAbstract();
    }
}
