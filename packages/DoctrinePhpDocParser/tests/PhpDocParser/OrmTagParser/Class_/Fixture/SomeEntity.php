<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Tests\PhpDocParser\OrmTagParser\Class_\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=true, repositoryClass="Rector\DoctrinePhpDocParser\Tests\PhpDocParser\OrmTagParser\Class_\Source\ExistingRepositoryClass")
 * @ORM\Entity
 * @ORM\Entity()
 * @ORM\Table(name="answer")
 */
final class SomeEntity
{

}
