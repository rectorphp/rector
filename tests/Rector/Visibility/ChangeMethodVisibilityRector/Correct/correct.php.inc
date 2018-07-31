<?php declare (strict_types=1);

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
