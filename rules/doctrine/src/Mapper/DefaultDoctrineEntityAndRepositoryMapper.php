<?php

declare(strict_types=1);

namespace Rector\Doctrine\Mapper;

use Nette\Utils\Strings;
use Rector\Doctrine\Contract\Mapper\DoctrineEntityAndRepositoryMapperInterface;

final class DefaultDoctrineEntityAndRepositoryMapper implements DoctrineEntityAndRepositoryMapperInterface
{
    /**
     * @var string
     */
    private const REPOSITORY = 'Repository';

    public function mapRepositoryToEntity(string $repository): ?string
    {
        // "SomeRepository" => "Some"
        $withoutSuffix = Strings::substring($repository, 0, - strlen(self::REPOSITORY));

        // "App\Repository\Some" => "App\Entity\Some"
        return Strings::replace($withoutSuffix, '#Repository#', 'Entity');
    }

    public function mapEntityToRepository(string $entity): ?string
    {
        // "Some" => "SomeRepository"
        $withSuffix = $entity . self::REPOSITORY;

        // "App\Entity\SomeRepository" => "App\Repository\SomeRepository"
        return Strings::replace($withSuffix, '#Entity#', self::REPOSITORY);
    }
}
