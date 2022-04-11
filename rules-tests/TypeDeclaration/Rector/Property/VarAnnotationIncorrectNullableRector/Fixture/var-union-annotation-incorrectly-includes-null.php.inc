<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarUnionAnnotationIncorrectlyIncludesNull
{
    /**
     * @var \DateTime[]|\DateTimeImmutable[]|null
     */
    private array $dateTimes;
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarUnionAnnotationIncorrectlyIncludesNull
{
    /**
     * @var \DateTime[]|\DateTimeImmutable[]
     */
    private array $dateTimes;
}
?>
