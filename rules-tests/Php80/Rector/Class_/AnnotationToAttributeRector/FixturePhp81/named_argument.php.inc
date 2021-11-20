<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class NamedArgument
{
    /**
     * @Assert\All({
     *     @Assert\NotNumber(secondValue=1000)
     * })
     */
    public $value;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class NamedArgument
{
    #[Assert\All(nestedAsserts: [new Assert\NotNumber(secondValue: 1000)])]
    public $value;
}

?>
