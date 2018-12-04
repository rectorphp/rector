<?php

use Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source\ParentObject;

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

use Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source\ParentObject;

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
