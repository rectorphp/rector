<?php

namespace Rector\Renaming\Tests\Rector\Namespace_\RenameNamespaceRector\Fixture;

use OldNamespace;
use OldNamespace\AnotherSubNamespace;

class SomeClass
{
    public function someClass()
    {
        $keepThis = new \OldNamespace;

        return new \OldNamespace\SomeClass;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Namespace_\RenameNamespaceRector\Fixture;

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

?>
