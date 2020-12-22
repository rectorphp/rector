<?php

namespace Rector\Renaming\Tests\Rector\Namespace_\RenameNamespaceRector\Fixture;

use OldNamespace;

class SomeClass2
{
    public function someClass(OldNamespace\SubNamespace $argument)
    {
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Namespace_\RenameNamespaceRector\Fixture;

use NewNamespace;

class SomeClass2
{
    public function someClass(\NewNamespace\SubNamespace $argument)
    {
    }
}

?>
