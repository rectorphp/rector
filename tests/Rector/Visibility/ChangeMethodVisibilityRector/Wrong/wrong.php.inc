<?php declare (strict_types=1);

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
