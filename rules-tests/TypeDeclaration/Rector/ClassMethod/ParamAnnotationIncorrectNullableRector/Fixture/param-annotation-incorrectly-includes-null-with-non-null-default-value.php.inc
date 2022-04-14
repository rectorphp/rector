<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIncorrectlyIncludesNullWithNonNullDefaultValue
{
    /**
     * @param bool|null $flag
     */
    public function setFlag(bool $flag = true): self
    {
        $this->flag = $flag;

        return $this;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIncorrectlyIncludesNullWithNonNullDefaultValue
{
    /**
     * @param bool $flag
     */
    public function setFlag(bool $flag = true): self
    {
        $this->flag = $flag;

        return $this;
    }
}
?>
