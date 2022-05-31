<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\OpenApi\Annotation as OA;

final class AliasUsedWithUsePartialRename
{
    /**
     * @OA\NestedPastAnnotation
     */
    public $value;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\OpenApi\Attribute as OA;

final class AliasUsedWithUsePartialRename
{
    #[OA\NestedFutureAttribute]
    public $value;
}

?>
