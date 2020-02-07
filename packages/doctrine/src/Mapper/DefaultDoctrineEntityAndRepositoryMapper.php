<?php

declare(strict_types=1);

namespace Rector\Doctrine\Mapper;

use Nette\Utils\Strings;
use Rector\Doctrine\Contract\Mapper\DoctrineEntityAndRepositoryMapperInterface;

final class DefaultDoctrineEntityAndRepositoryMapper implements DoctrineEntityAndRepositoryMapperInterface
{
    public function mapRepositoryToEntity(string $repository): ?string
    {
        // "SomeRepository" => "Some"
        $withoutSuffix = Strings::substring($repository, 0, - strlen('Repository'));

        // "App\Repository\Some" => "App\Entity\Some"
        return Strings::replace($withoutSuffix, '#Repository#', 'Entity');
    }

    public function mapEntityToRepository(string $entity): ?string
    {
        // "Some" => "SomeRepository"
        $withSuffix = $entity . 'Repository';

        // "App\Entity\SomeRepository" => "App\Repository\SomeRepository"
        return Strings::replace($withSuffix, '#Entity#', 'Repository');
    }
}
