<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\Doctrine\ObjectRepository;
use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\Entity\SomeEntity;

final class SkipRepositoryGeneric
{
    /**
     * @var ObjectRepository<SomeEntity>
     */
     private $timestampableRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timestampableRepository = $this->entityManager->getRepository(SomeEntity::class);
    }
}
