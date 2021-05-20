<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD", "PROPERTY", "CLASS"})
 */
final class TargetMethodPropertyClass
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Target;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
final class TargetMethodPropertyClass
{
}

?>
