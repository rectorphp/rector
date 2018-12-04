<?php

namespace NewNamespace\SubNamespace;

use OldNamespaceWith\OldSplitNamespace;

class SomeClass3
{
    public function someClass()
    {
        return new OldSplitNamespace\SomeClass3;
    }
}

?>
-----
<?php

namespace NewNamespace\SubNamespace;

use NewNamespaceWith\NewSplitNamespace;

class SomeClass3
{
    public function someClass()
    {
        return new \NewNamespaceWith\NewSplitNamespace\SomeClass3;
    }
}

?>
