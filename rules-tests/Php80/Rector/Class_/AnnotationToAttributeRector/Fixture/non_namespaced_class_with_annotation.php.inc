<?php

/**
 * @Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation(
 *     some="/demo/"
 * )
 */
final class NonNamespacedClassWithAnnotation
{
}
?>
-----
<?php

#[Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation(some: '/demo/')]
final class NonNamespacedClassWithAnnotation
{
}
?>
