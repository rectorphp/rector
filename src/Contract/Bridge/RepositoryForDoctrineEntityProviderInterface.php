<?php declare(strict_types=1);

namespace Rector\Contract\Bridge;

interface RepositoryForDoctrineEntityProviderInterface
{
    public function provideRepositoryForEntity(string $name): ?string;

    /**
     * @return string[]
     */
    public function provideRepositoriesForEntities(): array;
}
