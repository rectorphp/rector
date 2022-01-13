<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\ConstantReference;

/**
 * @GenericAnnotation(
 *     some={
 *          ConstantReference::FIRST_NAME={
 *              foo: {"bar"}
 *          },
 *          ConstantReference::LAST_NAME
 *     }
 * )
 * @GenericAnnotation(
 *     some={
 *          ConstantReference::LAST_NAME,
 *          trailingValue
 *     }
 * )
 */
final class ArrayWithConstantAsKey
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\ConstantReference;

#[GenericAnnotation(some: [ConstantReference::FIRST_NAME => ['foo' => ['bar']], 0 => ConstantReference::LAST_NAME])]
#[GenericAnnotation(some: [ConstantReference::LAST_NAME, 'trailingValue'])]
final class ArrayWithConstantAsKey
{
}

?>
