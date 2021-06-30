<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\Path;

/**
 * @Path("
 *     summary: Send webcam reward
 * ")
 */
final class MultilineContent
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\Path;

#[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\Path(path: '
    summary: Send webcam reward
')]
final class MultilineContent
{
}

?>
