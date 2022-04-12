<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

/**
 * @return \DateTime[]
 */
function getDateTimes(int $i): ?array
{
    return $i > 0 ? [new \DateTime()] : null;
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

/**
 * @return \DateTime[]|null
 */
function getDateTimes(int $i): ?array
{
    return $i > 0 ? [new \DateTime()] : null;
}

?>
