<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\RemoveEmptyTableAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table]
#[ORM\Entity]
class Product
{
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\AttributeGroup\RemoveEmptyTableAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product
{
}

?>
