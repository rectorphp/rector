<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

final class TrailingCommadAfterArray
{
    /**
     * @GenericAnnotation(
     *     "some" = "item",
     *     "summary" = "item",
     * )
     */
    public function action()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

final class TrailingCommadAfterArray
{
    #[GenericAnnotation(some: 'item', summary: 'item')]
    public function action()
    {
    }
}

?>
