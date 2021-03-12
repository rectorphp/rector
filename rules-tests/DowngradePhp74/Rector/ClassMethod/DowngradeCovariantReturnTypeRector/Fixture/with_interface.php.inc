<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceParentType;
use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceChildType;

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

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceParentType;
use Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceChildType;

interface WithInterfaceA
{
    public function covariantReturnTypes(): WithInterfaceParentType;
}

interface WithInterfaceB extends WithInterfaceA
{
    public function covariantReturnTypes(): \Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceParentType;
}

?>
