<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class VariousArguments
{
    /**
     * @Assert\All({
     *     @Assert\Length(1000),
     *     @Assert\NotNumber(hey=10, hi="hello")
     * })
     */
    public $value;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\FixturePhp81;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\SourcePhp81 as Assert;

final class VariousArguments
{
    #[Assert\All(nestedAsserts: [new Assert\Length(1000), new Assert\NotNumber(hey: 10, hi: 'hello')])]
    public $value;
}

?>
