<?php

declare(strict_types=1);

namespace Rector\Visibility\Tests\Rector\ClassMethod\ChangeMethodVisibilityRector\Source;

class ParentObject
{
    private function toBePublicMethod() {

    }

    static function toBePublicStaticMethod() {

    }

    protected function toBeProtectedMethod() {

    }
    private function toBePrivateMethod() {

    }
}
