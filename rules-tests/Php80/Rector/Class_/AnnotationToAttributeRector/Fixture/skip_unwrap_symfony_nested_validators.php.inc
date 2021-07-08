<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Symfony\Component\Validator\Constraints as Assert;

final class SkipUnwrapSymfonyNestedValidators
{
    /**
     * @GenericAnnotation("hey")
     *
     * @var array
     * @Assert\All(
     *     constraints={
     *         @GenericAnnotation()
     *     }
     * )
     */
    public $all;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Symfony\Component\Validator\Constraints as Assert;

final class SkipUnwrapSymfonyNestedValidators
{
    /**
     *
     * @var array
     * @Assert\All(
     *     constraints={
     *         @GenericAnnotation()
     *     }
     * )
     */
    #[\Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation(some: 'hey')]
    public $all;
}

?>
