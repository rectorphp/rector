<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class NullableParentType {}
class NullableChildType extends NullableParentType {}

class NullableA
{
    public function covariantReturnTypes(): ?NullableParentType
    { /* … */ }
}

class NullableB extends NullableA
{
    public function covariantReturnTypes(): ?NullableChildType
    { /* … */ }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class NullableParentType {}
class NullableChildType extends NullableParentType {}

class NullableA
{
    public function covariantReturnTypes(): ?NullableParentType
    { /* … */ }
}

class NullableB extends NullableA
{
    /**
     * @return \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\NullableChildType|null
     */
    public function covariantReturnTypes(): ?\Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\NullableParentType
    { /* … */ }
}

?>
