<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class NestedAttributeWithBrackets
{
    /**
     * @Assert\All({
     *     @Assert\NotNull()
     * })
     */
    public $value;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class NestedAttributeWithBrackets
{
    #[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\All(nestedAsserts: [new \Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81\NotNull()])]
    public $value;
}

?>
