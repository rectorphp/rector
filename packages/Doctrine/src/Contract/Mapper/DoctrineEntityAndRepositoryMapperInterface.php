<?php

declare(strict_types=1);

namespace Rector\Doctrine\Contract\Mapper;

interface DoctrineEntityAndRepositoryMapperInterface
{
    public function mapRepositoryToEntity(string $name): ?string;

    public function mapEntityToRepository(string $name): ?string;
}
