<?php

namespace Rector\SOLID\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class ClassWithConstantVisibilityDeclared
{
    public const LOCAL_ONLY = true;

    public function isLocalOnly()
    {
        return self::LOCAL_ONLY;
    }
}

?>
