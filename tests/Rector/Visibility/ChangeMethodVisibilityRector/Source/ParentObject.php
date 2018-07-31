<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source;

class ParentObject
{
    public function toBePublicMethod() {

    }

    public function toBePublicStaticMethod() {

    }

    protected function toBeProtectedMethod() {

    }
    private function toBePrivateMethod() {

    }
}
