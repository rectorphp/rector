<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Source\GenericAnnotation;
use Symfony\Component\Validator\Constraints as Assert;

final class SkipUnwrapSymfonyNestedValidators
{
    /**
     * @var array
     * @Assert\All(
     *     constraints={
     *         @GenericAnnotation("firstConstraint"),
     *         @GenericAnnotation("secondConstraint")
     *     }
     * )
     */
    public $all;

    /**
     * @Assert\Sequentially({
     *     @GenericAnnotation("stepOne"),
     *     @GenericAnnotation("stepTwo")
     *   }
     * )
     */
    public $sequentially;

    /**
     * @Assert\AtLeastOneOf({
     *     @GenericAnnotation("thisOne"),
     *     @GenericAnnotation("orThisOne")
     * })
     */
    public $atLeastOneOf;

    /**
     * @var array
     *
     * @Assert\Collection(
     *     fields = {
     *         "email" = @GenericAnnotation("email"),
     *         "password" = @GenericAnnotation("password"),
     *     }
     * )
     */
    public $collection;
}
?>
