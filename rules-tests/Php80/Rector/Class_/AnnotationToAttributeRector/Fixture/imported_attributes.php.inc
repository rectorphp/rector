<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(
 *     fields={"parent", "code"},
 *     errorPath="code",
 *     message="Already exists!",
 *     ignoreNull=false,
 *     groups={"default", "update"}
 * )
 */
#[Table('Foo')]
#[Entity]
class Foo
{
}
