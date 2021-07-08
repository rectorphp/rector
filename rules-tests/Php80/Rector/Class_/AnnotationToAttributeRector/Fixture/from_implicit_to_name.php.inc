<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

/**
 * @GenericAnnotation("/")
 */
final class FromImplicitToName
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

#[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation(some: '/')]
final class FromImplicitToName
{
}

?>
