<?php declare(strict_types=1);

namespace Rector\Contract\Bridge;

abstract class AbstractRepositoryForDoctrineEntityProvider implements RepositoryForDoctrineEntityProviderInterface
{
    final public function provideRepositoryForEntity(string $name): ?string
    {
        if ($this->isAlias($name)) {
            return $this->resoleFromAlias($name);
        }

        return $this->provideRepositoriesForEntities()[$name] ?? null;
    }

    private function isAlias(string $name): bool
    {
        return strpos($name, ':') !== false;
    }

    private function resoleFromAlias(string $name): ?string
    {
        [$namespaceAlias, $simpleClassName] = explode(':', $name, 2);

        $pattern = sprintf('/(%s{1}.*%s)/', $namespaceAlias, $simpleClassName);

        foreach ($this->provideRepositoriesForEntities() as $key => $value) {
            if (preg_match($pattern, $key)) {
                return $value;
            }
        }

        return null;
    }
}
