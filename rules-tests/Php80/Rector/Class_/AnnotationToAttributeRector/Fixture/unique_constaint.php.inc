<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @Entity(uniqueConstraints={@UniqueConstraint(name="ean", columns={"ean"})})
 */
class ECommerceProduct
{
}
