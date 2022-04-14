<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class SkipParamAnnotationComplexOnPhpdocParserFailure
{
    /**
     * Parser fails to interpret annotations when there is no comma after AppAssert\Country in Assert\All, this is likely a bug in rector or one
     * of its dependencies. But we will skip these cases safely, so no worries.
     *
     * @OA\Property(property="dateTimes[]", default="null")
     * @Serializer\Groups({"export"})
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @AppAssert\Country
     * })
     * @Serializer\VirtualProperty
     * @param \DateTime[] $dateTimes
     */
    public function setDateTimes(?array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
