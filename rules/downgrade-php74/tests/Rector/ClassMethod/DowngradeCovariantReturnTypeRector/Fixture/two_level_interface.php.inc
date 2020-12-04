<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class TwoLevelInterfaceParentType {}
class TwoLevelInterfaceChildType extends TwoLevelInterfaceParentType {}

interface TwoLevelInterfaceA
{
    public function covariantReturnTypes(): TwoLevelInterfaceParentType;
}

interface TwoLevelInterfaceB extends TwoLevelInterfaceA
{
}

interface TwoLevelInterfaceC extends TwoLevelInterfaceB
{
    public function covariantReturnTypes(): TwoLevelInterfaceChildType;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class TwoLevelInterfaceParentType {}
class TwoLevelInterfaceChildType extends TwoLevelInterfaceParentType {}

interface TwoLevelInterfaceA
{
    public function covariantReturnTypes(): TwoLevelInterfaceParentType;
}

interface TwoLevelInterfaceB extends TwoLevelInterfaceA
{
}

interface TwoLevelInterfaceC extends TwoLevelInterfaceB
{
    /**
     * @return \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\TwoLevelInterfaceChildType
     */
    public function covariantReturnTypes(): \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\TwoLevelInterfaceParentType;
}

?>
