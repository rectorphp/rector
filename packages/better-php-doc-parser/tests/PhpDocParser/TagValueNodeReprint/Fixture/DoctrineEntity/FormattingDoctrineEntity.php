<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="my_entity",
 *     indexes={
 *         @ORM\Index(
 *             name="my_entity_idx", columns={"x", "xx", "xxx", "xxxx"}
 *         ),
 *         @ORM\Index(
 *             name="my_entity_xxx_idx", columns={"xxx"}
 *         )
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\MyEntityRepository")
 */
final class FormattingDoctrineEntity
{
}
