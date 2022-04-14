<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIncorrectlyIncludesNullWithNullDefaultValueFromConst
{
    private const FLAG_DEFAULT_VALUE = null;

    /**
     * @param bool $flag
     */
    public function setFlag(bool $flag = self::FLAG_DEFAULT_VALUE): self
    {
        $this->flag = $flag;

        return $this;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class ParamAnnotationIncorrectlyIncludesNullWithNullDefaultValueFromConst
{
    private const FLAG_DEFAULT_VALUE = null;

    /**
     * @param bool|null $flag
     */
    public function setFlag(bool $flag = self::FLAG_DEFAULT_VALUE): self
    {
        $this->flag = $flag;

        return $this;
    }
}
?>
