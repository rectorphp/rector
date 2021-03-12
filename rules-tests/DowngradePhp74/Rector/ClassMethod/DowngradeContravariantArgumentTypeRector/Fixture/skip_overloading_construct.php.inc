<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\Fixture;

class SkipOverloadingConstructParentType {}
class SkipOverloadingConstructChildType extends SkipOverloadingConstructParentType {}

class SkipOverloadingConstructA
{
    public function __construct(SkipOverloadingConstructChildType $type)
    {
        /* … */
    }
}

class SkipOverloadingConstructB extends SkipOverloadingConstructA
{
    public function __construct(SkipOverloadingConstructParentType $type)
    {
        /* … */
        parent::__construct($type);
    }
}

?>
