<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineTable;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="my_entity", indexes={@ORM\Index(
 *     name="my_entity_xxx_idx", columns={"xxx"}
 * )})
 */
final class FormattingDoctrineEntity
{
}

?>
-----
PhpParser\Node\Stmt\Class_
-----
Doctrine\ORM\Mapping\Table
