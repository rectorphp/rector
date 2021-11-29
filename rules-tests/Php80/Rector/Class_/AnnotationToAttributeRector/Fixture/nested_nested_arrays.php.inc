<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

/**
 * @GenericAnnotation(title="sample", route={absolute=true, parameters={id="{id}"}}))
 */
final class NestedNestedArrays
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

#[GenericAnnotation(title: 'sample', route: ['absolute' => true, 'parameters' => ['id' => '{id}']])]
final class NestedNestedArrays
{
}

?>
