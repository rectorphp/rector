<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=true, repositoryClass="Rector\Tests\BetterPhpDocParser\PhpDocParser\DoctrineOrmTagParser\Source\ExistingRepositoryClass")
 * @ORM\Table(name="answer")
 */
final class SomeEntity
{

}

?>
-----
PhpParser\Node\Stmt\Class_
-----
Doctrine\ORM\Mapping\Entity
Doctrine\ORM\Mapping\Table
