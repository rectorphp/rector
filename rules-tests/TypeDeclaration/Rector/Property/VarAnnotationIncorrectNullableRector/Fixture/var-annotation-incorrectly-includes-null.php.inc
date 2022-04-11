<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIncorrectlyIncludesNull
{
    /**
     * @var \DateTime[]|null
     */
    private array $dateTimes;
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIncorrectlyIncludesNull
{
    /**
     * @var \DateTime[]
     */
    private array $dateTimes;
}
?>
