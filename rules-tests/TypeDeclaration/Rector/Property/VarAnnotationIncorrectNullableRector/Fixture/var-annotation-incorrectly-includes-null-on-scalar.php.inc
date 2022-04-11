<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIncorrectlyIncludesNullOnScalar
{
    /**
     * @var string|null
     */
    private string $text;
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIncorrectlyIncludesNullOnScalar
{
    /**
     * @var string
     */
    private string $text;
}
?>
