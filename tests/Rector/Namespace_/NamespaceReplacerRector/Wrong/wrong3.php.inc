<?php declare(strict_types=1);

namespace NewNamespace\SubNamespace;

use OldNamespaceWith\OldSplitNamespace;

class SomeClass
{
    public function someClass()
    {
        return new OldSplitNamespace\SomeClass;
    }
}
