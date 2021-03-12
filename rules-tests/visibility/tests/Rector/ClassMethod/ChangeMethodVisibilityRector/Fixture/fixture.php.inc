<?php

use Rector\Visibility\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    private function toBePublicMethod() {

    }
    public function toBeProtectedMethod() {

    }
    protected function toBePrivateMethod() {

    }
}

?>
-----
<?php

use Rector\Visibility\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source\ParentObject;

class ClassThatInherits extends ParentObject
{
    public function toBePublicMethod() {

    }
    protected function toBeProtectedMethod() {

    }
    private function toBePrivateMethod() {

    }
}

?>
