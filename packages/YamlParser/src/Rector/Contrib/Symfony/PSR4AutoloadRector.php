<?php declare(strict_types=1);

namespace Rector\YamlParser\Rector\Contrib\Symfony;

use Nette\Utils\Strings;
use Rector\YamlParser\Contract\Rector\YamlRectorInterface;

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
    public function refactor(array $services): array
    {
        $classNames = [];

        // find namespace => resource ideal match
        foreach ($services as $name => $service) {
            $classNames[] = $service['class'] ?? $name;
        }

        $namespacePrefixes = [];
        foreach ($classNames as $className) {
            $namespacePrefixes[] = $this->resolveNamespacePrefix($className);
        }

        // remove already loaded classes
        foreach ($services as $name => $service) {
            $className = $service['class'] ?? $name;
            if ($this->isClassCoveredInNamespacePrefixes($className, $namespacePrefixes)) {
                unset($services[$name]);
            }
        }

        return $this->prependResources($services, $namespacePrefixes);
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
        foreach ($namespacePrefixes as $namespacePrefix) {
            $services[$namespacePrefix] = [
                'resource' => '..',
            ];
        }

        return $services;
    }
}
