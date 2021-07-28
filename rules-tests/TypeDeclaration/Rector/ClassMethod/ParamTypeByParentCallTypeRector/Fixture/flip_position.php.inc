<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Source\SomeControl;

final class FlipPosition extends SomeControl
{
    public function __construct($hey, $name)
    {
        parent::__construct($name);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Source\SomeControl;

final class FlipPosition extends SomeControl
{
    public function __construct($hey, string $name)
    {
        parent::__construct($name);
    }
}

?>
