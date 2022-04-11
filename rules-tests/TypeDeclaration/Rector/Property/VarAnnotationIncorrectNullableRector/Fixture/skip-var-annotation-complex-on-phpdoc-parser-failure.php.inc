<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class SkipVarAnnotationComplexOnPhpdocParserFailure
{
    /**
     * Parser fails to interpret annotations when there is no comma after AppAssert\Country in Assert\All, this is likely a bug in rector or one
     * of its dependencies. But we will skip these cases safely, so no worries.
     *
     * @OA\Property(property="countryCodes[]", default="null", example="CZ")
     * @Serializer\Groups({"export"})
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @AppAssert\Country
     * })
     * @Serializer\Type("array<string>")
     * @var string[]
     */
    private ?array $countryCodes = null;
}
