<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class SomeAnnotation
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Target;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SomeAnnotation
{
}

?>
