<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[@ORM\Entity]
class SomeClass
{
}

?>
