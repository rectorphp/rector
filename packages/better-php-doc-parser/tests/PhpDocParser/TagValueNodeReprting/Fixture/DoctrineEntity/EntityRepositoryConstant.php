<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\DoctrineOrmTagParser\Fixture\Class_\DoctrineEntity;

use Doctrine\ORM\Mapping as ORM;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\DoctrineOrmTagParser\Source\ExistingRepositoryClass;

/**
 * @ORM\Entity(repositoryClass=ExistingRepositoryClass::class)
 */
final class EntityRepositoryConstant
{
}
