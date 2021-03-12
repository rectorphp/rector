<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\TwoLevelInterfaceParentType;
}

?>
