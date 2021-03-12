<?php

use Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Source\Keep_This;

class Rector_Tests_Renaming_Rector_FileWithoutNamespace_PseudoNamespaceToNamespaceRector_Fixture_UseStatement
{
    public function run()
    {
        return new Keep_This;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

use Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Source\Keep_This;
class UseStatement
{
    public function run()
    {
        return new Keep_This;
    }
}
