<?php

use Rector\Visibility\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;

class ClassThatInherits2 extends ParentObject
{
    static function toBePublicStaticMethod() {

    }
}

?>
-----
<?php

use Rector\Visibility\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;

class ClassThatInherits2 extends ParentObject
{
    public static function toBePublicStaticMethod() {

    }
}

?>
