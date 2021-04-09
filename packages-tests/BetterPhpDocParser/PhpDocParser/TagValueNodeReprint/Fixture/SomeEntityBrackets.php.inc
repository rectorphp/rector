<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
final class SomeEntityBrackets
{

}

?>
-----
PhpParser\Node\Stmt\Class_
-----
Doctrine\ORM\Mapping\Entity
