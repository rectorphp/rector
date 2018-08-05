<?php declare(strict_types=1);

namespace NewNamespace\SubNamespace;

use NewNamespaceWith\NewSplitNamespace;

class SomeClass
{
    public function someClass()
    {
        return new \NewNamespaceWith\NewSplitNamespace\SomeClass;
    }
}
