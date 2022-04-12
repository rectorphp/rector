<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

/**
 * @return \DateTime[]|null
 */
function getDateTimes(): array
{
    return [new \DateTime()];
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

/**
 * @return \DateTime[]
 */
function getDateTimes(): array
{
    return [new \DateTime()];
}

?>
