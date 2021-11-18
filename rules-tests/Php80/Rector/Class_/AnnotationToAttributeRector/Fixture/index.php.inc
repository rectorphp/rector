<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;

/**
 * @Entity(indexes={@Index(name="search_idx", columns={"name", "c"})}]
 */
class ECommerceProduct
{
}
