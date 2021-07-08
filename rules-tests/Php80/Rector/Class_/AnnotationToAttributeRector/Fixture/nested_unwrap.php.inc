<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Responses;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Response;

final class NestedUnwrap
{
    /**
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

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Responses;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Response;

final class NestedUnwrap
{
    #[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Response(code: '200', description: 'Contests', entity: ContestListView::class)]
    public function action()
    {
    }
}

?>
