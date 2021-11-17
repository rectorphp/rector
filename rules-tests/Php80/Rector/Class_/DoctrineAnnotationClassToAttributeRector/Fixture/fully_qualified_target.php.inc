<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

/**
 * @Annotation
 * @\Doctrine\Common\Annotations\Annotation\Target("METHOD")
 */
final class FullyQualifiedTarget
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class FullyQualifiedTarget
{
}

?>
