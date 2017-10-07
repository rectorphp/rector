<?php declare(strict_types=1);

namespace NewNamespace\SubNamespace;

use NewNamespace;
use NewNamespace\AnotherSubNamespace;

class SomeClass
{
    public function someClass()
    {
        $keepThis = new \OldNamespace;

        return new \NewNamespace\SomeClass;
    }
}
