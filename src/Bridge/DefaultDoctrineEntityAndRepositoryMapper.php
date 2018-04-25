<?php declare(strict_types=1);

namespace Rector\Bridge;

use Nette\Utils\Strings;
use Rector\Contract\Bridge\DoctrineEntityAndRepositoryMapperInterface;

final class DefaultDoctrineEntityAndRepositoryMapper implements DoctrineEntityAndRepositoryMapperInterface
{
    public function mapRepositoryToEntity(string $repository): ?string
    {
        // "SomeRepository" => "Some"
        $withoutSuffix = substr($repository, 0, - strlen('Repository'));

        // "App\Repository\Some" => "App\Entity\Some"
        return Strings::replace($withoutSuffix, '#Repository#', 'Entity');
    }

    public function mapEntityToRepository(string $entity): ?string
    {
        if ($this->isAlias($entity)) {
            return $this->resoleFromAlias($entity);
        }

        // "Some" => "SomeRepository"
        $withSuffix = $entity . 'Repository';

        // "App\Entity\SomeRepository" => "App\Repository\SomeRepository"
        return Strings::replace($withSuffix, '#Entity#', 'Repository');
    }

    private function isAlias(string $name): bool
    {
        return strpos($name, ':') !== false;
    }

    private function resoleFromAlias(string $name): ?string
    {
        [$namespaceAlias, $simpleClassName] = explode(':', $name, 2);

        $pattern = sprintf('/(%s{1}.*%s)/', $namespaceAlias, $simpleClassName);

        // @todo fixure out DoctrineBundle internals for such namings rather than provide list manually
        foreach ($this->provideRepositoriesForEntities() as $key => $value) {
            if (preg_match($pattern, $key)) {
                return $value;
            }
        }

        return null;
    }
}
