<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class NestedAttributesWithArgument
{
    /**
     * @Assert\All({
     *     @Assert\NotNumber(1000)
     * })
     */
    public $value;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class NestedAttributesWithArgument
{
    #[Assert\All(nestedAsserts: [new Assert\NotNumber(1000)])]
    public $value;
}

?>
