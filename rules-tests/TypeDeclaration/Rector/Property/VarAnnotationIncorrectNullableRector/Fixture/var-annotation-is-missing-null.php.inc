<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIsMissingNull
{
    /**
     * @var \DateTime[]
     */
    private ?array $dateTimes;
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\Fixture;

final class VarAnnotationIsMissingNull
{
    /**
     * @var \DateTime[]|null
     */
    private ?array $dateTimes;
}
?>
