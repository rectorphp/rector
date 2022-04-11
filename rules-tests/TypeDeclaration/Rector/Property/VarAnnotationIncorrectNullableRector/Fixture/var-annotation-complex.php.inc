<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationComplex
{
    /**
     * @OA\Property(property="countryCodes[]", default="null", example="CZ")
     * @Serializer\Groups({"export"})
     * @Serializer\Type("array<string>")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @AppAssert\Country,
     * })
     * @var string[]
     */
    private ?array $countryCodes = null;
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationComplex
{
    /**
     * @OA\Property(property="countryCodes[]", default="null", example="CZ")
     * @Serializer\Groups({"export"})
     * @Serializer\Type("array<string>")
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @AppAssert\Country,
     * })
     * @var string[]|null
     */
    private ?array $countryCodes = null;
}
?>
