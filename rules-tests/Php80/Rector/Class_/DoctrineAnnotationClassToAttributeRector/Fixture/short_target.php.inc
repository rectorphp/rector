<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class ShortTarget
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\DoctrineAnnotationClassToAttributeRector\Fixture;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class ShortTarget
{
}

?>
