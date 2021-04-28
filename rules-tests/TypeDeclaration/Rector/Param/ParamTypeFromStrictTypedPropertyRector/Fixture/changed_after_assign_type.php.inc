<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class ChangedAfterAssignType
{
    private array $kind;

    public function setAge($kind)
    {
        $this->kind = $kind;

        if (!is_array($kind)) {
            $kind = [$kind];
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\Fixture;

final class ChangedAfterAssignType
{
    private array $kind;

    public function setAge(array $kind)
    {
        $this->kind = $kind;

        if (!is_array($kind)) {
            $kind = [$kind];
        }
    }
}

?>
