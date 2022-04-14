<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

/**
 * @param \DateTime[] $dateTimes
 */
function reverseDateTimes(?array $dateTimes): array
{
    return array_reverse($dateTimes);
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

/**
 * @param \DateTime[]|null $dateTimes
 */
function reverseDateTimes(?array $dateTimes): array
{
    return array_reverse($dateTimes);
}

?>
