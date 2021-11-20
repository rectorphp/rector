<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Validation\NotNull;
use Symfony\Component\Validator\Constraints as Assert;

final class SkipUnwrapSymfonyNestedValidators
{
    /**
     * @GenericAnnotation("hey")
     *
     * @var array
     * @Assert\All(
     *     constraints={
     *         @NotNull()
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
use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\Validation\NotNull;
use Symfony\Component\Validator\Constraints as Assert;

final class SkipUnwrapSymfonyNestedValidators
{
    /**
     *
     * @var array
     * @Assert\All(
     *     constraints={
     *         @NotNull()
     *     }
     * )
     */
    #[GenericAnnotation(some: 'hey')]
    public $all;
}

?>
