<?php declare(strict_types=1);

namespace Rector\Contract\Bridge;

interface RepositoryForDoctrineEntityProviderInterface
{
    public function mapEntityToRepository(string $name): ?string;
}
