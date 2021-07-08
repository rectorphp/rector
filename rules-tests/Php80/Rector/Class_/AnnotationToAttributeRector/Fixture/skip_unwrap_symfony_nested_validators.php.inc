<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class SkipUnwrapSymfonyNestedValidators
{
    /**
     * @var array
     * @Assert\All(
     *     constraints={
     *         @Assert\NotNull()
     *     }
     * )
     */
    public $all;
}
