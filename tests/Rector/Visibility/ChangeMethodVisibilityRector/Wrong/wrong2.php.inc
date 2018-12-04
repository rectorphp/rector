<?php

use Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source\ParentObject;

class ClassThatInherits2 extends ParentObject
{
    static function toBePublicStaticMethod() {

    }
}

?>
-----
<?php

use Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source\ParentObject;

class ClassThatInherits2 extends ParentObject
{
    public function toBePublicStaticMethod() {

    }
}

?>
