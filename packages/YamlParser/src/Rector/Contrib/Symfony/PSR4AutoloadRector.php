<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector\Contrib\Symfony;

use Nette\Utils\Strings;
use Rector\YamlParser\Contract\Rector\YamlRectorInterface;
use ReflectionClass;
use SplFileInfo;

final class PSR4AutoloadRector implements YamlRectorInterface
{
    public function getCandidateKey(): string
    {
        return 'services';
    }

    /**
     * @param mixed[] $services
     * @return mixed[]
     */
    public function refactor(array $services, SplFileInfo $fileInfo): array
    {
        $classNames = $this->resolveClassNamesFromServices($services);
        $namespacePrefixes = $this->resolveNamespacePrefixesFromClassNames($classNames);

        // remove already loaded classes
        foreach ($services as $name => $service) {
            $className = $service['class'] ?? $name;
            if ($this->isClassCoveredInNamespacePrefixes($className, $namespacePrefixes)) {
                unset($services[$name]);
            }
        }

        $namespacePrefixes = $this->resolveResourcesForNamespacePrefixes($namespacePrefixes, $fileInfo);

        return $this->prependResources($services, $namespacePrefixes);
    }

    /**
     * @param mixed[] $service
     * @param string[] $namespacePrefixes
     */
    private function isClassCoveredInNamespacePrefixes(string $className, array $namespacePrefixes): bool
    {
        foreach ($namespacePrefixes as $namespacePrefix) {
            if (Strings::startsWith($className, $namespacePrefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed[] $services
     * @param string[] $namespacePrefixes
     * @return mixed[]
     */
    private function prependResources(array $services, array $namespacePrefixes): array
    {
        $namespacePrefixes = array_unique($namespacePrefixes);
        foreach ($namespacePrefixes as $namespacePrefix => $resource) {
            $services[$namespacePrefix] = [
                'resource' => $resource
            ];
        }

        return $services;
    }

    /**
     * @param mixed[] $services
     * @return string[]
     */
    private function resolveClassNamesFromServices(array $services): array
    {
        $classNames = [];

        // find namespace => resource ideal match
        foreach ($services as $name => $service) {
            $classNames[] = $service['class'] ?? $name;
        }

        return $classNames;
    }

    /**
     * @param string[] $classNames
     * @return string[]
     */
    private function resolveNamespacePrefixesFromClassNames(array $classNames): array
    {
        $namespacePrefixes = [];

        foreach ($classNames as $className) {
            $namespacePrefixes[$className] = $this->resolveNamespacePrefix($className);
        }

        return array_unique($namespacePrefixes);
    }

    private function resolveNamespacePrefix(string $className): string
    {
        $classNameParts = explode('\\', $className);

        if (count($classNameParts) === 1) {
            return $classNameParts[0] . '\\';
        }

        return $classNameParts[0] . '\\' . $classNameParts[1] . '\\';
    }

    /**
     * @param string[] $namespacePrefixes
     * @return string[]
     */
    private function resolveResourcesForNamespacePrefixes(array $namespacePrefixes, SplFileInfo $fileInfo): array
    {
        $namespacePrefixesToResources = [];

        foreach ($namespacePrefixes as $class => $namespacePrefix) {
            if (class_exists($class)) {
                $classPath = (new ReflectionClass($class))->getFileName();

                // how many directories were traversed up to namespace
                $climbedDirectoryCount = substr_count($class, '\\') - substr_count($namespacePrefix, '\\');

                // apply same traverse to directory
                $dirname = dirname($classPath);
                for ($i = $climbedDirectoryCount; $i > 0; --$i) {
                    $dirname = dirname($dirname);
                }

                // directory where current config is located
                $sourceDirectory = $fileInfo->getPath();

                // path from namespace prefix to current config
                $relativePathFomrNamespacePrefixToConfig = substr($sourceDirectory, strlen($dirname));

                $resourceClimbedLevels = substr_count($relativePathFomrNamespacePrefixToConfig, '/');

                $resource = '';
                for ($i = $resourceClimbedLevels; $i > 0; --$i) {
                    $resource .= '../';
                }

                $namespacePrefixesToResources[$namespacePrefix] = $resource;
            }
        }

        return $namespacePrefixesToResources;
    }
}
