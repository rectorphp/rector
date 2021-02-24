<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

use Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceParentType;
use Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceChildType;

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

use Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceParentType;
use Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceChildType;

interface WithInterfaceA
{
    public function covariantReturnTypes(): WithInterfaceParentType;
}

interface WithInterfaceB extends WithInterfaceA
{
    public function covariantReturnTypes(): \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Source\WithInterfaceParentType;
}

?>
