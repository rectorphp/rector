<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineEntity;

use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\BetterPhpDocParser\PhpDocParser\DoctrineOrmTagParser\Source\ExistingRepositoryClass;

/**
 * @ORM\Entity(repositoryClass=ExistingRepositoryClass::class)
 */
final class EntityRepositoryConstant
{
}
