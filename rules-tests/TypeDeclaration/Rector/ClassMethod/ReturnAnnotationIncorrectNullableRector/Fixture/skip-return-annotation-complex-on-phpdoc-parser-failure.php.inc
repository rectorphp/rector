<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class SkipReturnAnnotationComplexOnPhpdocParserFailure
{
    /**
     * @Serializer\VirtualProperty
     * @Serializer\Type("array<DateTime>")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @AppAssert\Country
     * })
     * @return \DateTime[]
     */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
    }
}
