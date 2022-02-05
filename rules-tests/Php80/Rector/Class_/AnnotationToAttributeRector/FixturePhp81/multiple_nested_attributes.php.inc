<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class MultipleNestedAttribute
{
    /**
     * @Assert\All({
     *     @Assert\NotNull(),
     *     @Assert\NotNumber(),
     * })
     */
    public $value;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class MultipleNestedAttribute
{
    #[Assert\All([new Assert\NotNull(), new Assert\NotNumber()])]
    public $value;
}

?>
