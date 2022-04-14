<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class InlineVarAnnotationWithinComplexComment
{
    /**
     * @Serializer\VirtualProperty
     * @Serializer\Type("array<DateTime>")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @AppAssert\Country,
     * })
     * @var \DateTime[] $dateTimes
     */
    private array $dateTimes;
}
?>
