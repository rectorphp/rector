<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\DoctrineTable;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=true, repositoryClass="Rector\BetterPhpDocParser\Tests\PhpDocParser\DoctrineOrmTagParser\Source\ExistingRepositoryClass")
 * @ORM\Table(name="answer")
 */
final class SomeEntity
{

}
