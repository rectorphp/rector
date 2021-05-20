<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class SomeAnnotation
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Target;

#[\Attribute(\Attribute::TARGET_METHOD)]
class SomeAnnotation
{
}

?>
