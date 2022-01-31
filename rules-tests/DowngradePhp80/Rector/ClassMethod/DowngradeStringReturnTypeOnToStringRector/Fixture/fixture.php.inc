<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\Source\ParentClassWithToString;

class Fixture extends ParentClassWithToString
{
    public function __toString()
    {
        return 'value';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\Fixture;

use Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeStringReturnTypeOnToStringRector\Source\ParentClassWithToString;

class Fixture extends ParentClassWithToString
{
    public function __toString(): string
    {
        return 'value';
    }
}

?>
