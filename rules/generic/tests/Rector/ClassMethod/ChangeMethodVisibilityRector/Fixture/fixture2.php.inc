<?php

use Rector\Generic\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;

class ClassThatInherits2 extends ParentObject
{
    static function toBePublicStaticMethod() {

    }
}

?>
-----
<?php

use Rector\Generic\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;

class ClassThatInherits2 extends ParentObject
{
    public function toBePublicStaticMethod() {

    }
}

?>
