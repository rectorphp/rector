<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\ArrayWrapper;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Responses;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Response;

final class NestedUnwrap
{
    /**
     * @ArrayWrapper({
     *     @GenericAnnotation("yes")
     * })
     * @Responses({
     *     @Response(code="200", description="Contests", entity=ContestListView::class),
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
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Responses;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Response;

final class NestedUnwrap
{
    /**
     * @ArrayWrapper({
     *     @GenericAnnotation("yes")
     * })
     * @Responses({
     *     @Response(code="200", description="Contests", entity=ContestListView::class),
     * })
     */
    #[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation('yes')]
    #[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Response(code: '200', description: 'Contests', entity: ContestListView::class)]
    public function action()
    {
    }
}

?>
