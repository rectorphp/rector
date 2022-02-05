<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericSingleImplicitAnnotation;

/**
 * @GenericSingleImplicitAnnotation("/")
 */
final class FromImplicitToName
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericSingleImplicitAnnotation;

#[GenericSingleImplicitAnnotation('/')]
final class FromImplicitToName
{
}

?>
