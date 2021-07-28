<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Source\SomeControl;

final class VideoControl extends SomeControl
{
    public function __construct($name)
    {
        parent::__construct($name);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector\Source\SomeControl;

final class VideoControl extends SomeControl
{
    public function __construct(string $name)
    {
        parent::__construct($name);
    }
}

?>
