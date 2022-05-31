<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Annotation\OpenApi as OA;

final class AliasUsedWithUsePartialRename
{
    /**
     * @OA\PastAnnotation
     */
    public $value;

    /**
     * @OA\PastAnnotation
     */
    public $secondValue;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Attribute\OpenApi as OA;

final class AliasUsedWithUsePartialRename
{
    #[OA\FutureAttribute]
    public $value;

    #[OA\FutureAttribute]
    public $secondValue;
}

?>
