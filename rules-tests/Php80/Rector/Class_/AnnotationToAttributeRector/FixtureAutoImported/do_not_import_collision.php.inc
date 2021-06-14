<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\Apple;

/**
 * @Apple
 */
final class DoNotImportCollision
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixtureAutoImported;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\Apple;

#[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\Apple]
final class DoNotImportCollision
{
}

?>
