<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIncorrectlyIncludesNullOnScalar
{
    /**
     * @param string|null $text
     */
    public function setDateTimes(string $text): self
    {
        $this->text = $text;

        return $this;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIncorrectlyIncludesNullOnScalar
{
    /**
     * @param string $text
     */
    public function setDateTimes(string $text): self
    {
        $this->text = $text;

        return $this;
    }
}
?>
