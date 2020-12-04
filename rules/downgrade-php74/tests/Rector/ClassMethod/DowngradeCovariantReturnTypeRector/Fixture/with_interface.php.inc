<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class WithInterfaceParentType {}
class WithInterfaceChildType extends WithInterfaceParentType {}

interface WithInterfaceA
{
    public function covariantReturnTypes(): WithInterfaceParentType;
}

interface WithInterfaceB extends WithInterfaceA
{
    public function covariantReturnTypes(): WithInterfaceChildType;
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class WithInterfaceParentType {}
class WithInterfaceChildType extends WithInterfaceParentType {}

interface WithInterfaceA
{
    public function covariantReturnTypes(): WithInterfaceParentType;
}

interface WithInterfaceB extends WithInterfaceA
{
    /**
     * @return \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\WithInterfaceChildType
     */
    public function covariantReturnTypes(): \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\WithInterfaceParentType;
}

?>
