<?php

/**
 * @Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation(
 *     routePrefix="/demo/"
 * )
 */
final class NonNamespacedClassWithAnnotation
{
}
?>
-----
<?php

#[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation(routePrefix: '/demo/')]
final class NonNamespacedClassWithAnnotation
{
}
?>
