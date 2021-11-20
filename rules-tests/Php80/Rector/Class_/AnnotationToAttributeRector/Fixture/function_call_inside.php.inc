<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;

final class FunctionCallInside
{
    /**
     * @GenericAnnotation("is_granted('ROLE_USER')")
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

final class FunctionCallInside
{
    #[GenericAnnotation(some: "is_granted('ROLE_USER')")]
    public function action()
    {
    }
}

?>
