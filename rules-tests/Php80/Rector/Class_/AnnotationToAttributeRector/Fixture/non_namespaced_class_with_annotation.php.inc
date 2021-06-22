<?php

/**
 * @ApiPlatform\Core\Annotation\ApiResource(
 *     routePrefix="/demo/"
 * )
 */
final class NonNamespacedClassWithAnnotation
{
}
?>
-----
<?php

#[\ApiPlatform\Core\Annotation\ApiResource(routePrefix: '/demo/')]
final class NonNamespacedClassWithAnnotation
{
}
?>
