<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

/**
 * @GenericAnnotation("sample", itemOperations="yes")
 */
final class FirstParamIsSilent
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

#[GenericAnnotation('sample', itemOperations: 'yes')]
final class FirstParamIsSilent
{
}

?>
