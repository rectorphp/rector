<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\ArrayWrapper;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

final class NestedUnwrap
{
    /**
     * @ArrayWrapper({
     *     @GenericAnnotation("yes")
     * })
     */
    public function action()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\ArrayWrapper;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

final class NestedUnwrap
{
    /**
     * @ArrayWrapper({
     *     @GenericAnnotation("yes")
     * })
     */
    #[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation('yes')]
    public function action()
    {
    }
}

?>
