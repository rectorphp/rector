<?php

namespace Rector\Renaming\Tests\Rector\Namespace_\RenameNamespaceRector\Fixture;

class Fixture5
{
    public function someClass()
    {
        return new SomeClass5;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Namespace_\RenameNamespaceRector\Fixture;

class Fixture5
{
    public function someClass()
    {
        return new \Short\AnyNamespace\SomeClass5;
    }
}

?>
