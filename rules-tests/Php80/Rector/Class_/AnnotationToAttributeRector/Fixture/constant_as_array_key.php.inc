<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\ConstantReference;

/**
 * @GenericAnnotation(
 *     itemOperations={
 *         "get" = {
               ConstantReference::FIRST_NAME = true
 *         }
 *     }
 * )
 */
final class DemoClass {
}
?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\ConstantReference;

#[GenericAnnotation(itemOperations: ['get' => [ConstantReference::FIRST_NAME => true]])]
final class DemoClass
{
}
?>
