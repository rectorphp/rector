<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class SkipIllegalOffsetType
{
    /**
     * @Assert\All(
     *     constraints={
     *         121 = "test"
     *     }
     * )
     */
    public $all;
}

?>
