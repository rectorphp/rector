<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnAnnotationIncorrectlyIncludesNullOnScalar
{
    /**
     * @return string|null
     */
    public function getDateTimes(): array
    {
        return $this->text;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\Fixture;

final class ReturnAnnotationIncorrectlyIncludesNullOnScalar
{
    /**
     * @return string
     */
    public function getDateTimes(): array
    {
        return $this->text;
    }
}
?>
