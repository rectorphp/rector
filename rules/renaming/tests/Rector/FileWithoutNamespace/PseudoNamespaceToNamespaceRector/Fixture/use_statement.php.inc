<?php

use Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Source\Keep_This;

class Rector_Renaming_Tests_Rector_FileWithoutNamespace_PseudoNamespaceToNamespaceRector_Fixture_UseStatement
{
    public function run()
    {
        return new Keep_This;
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

use Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Source\Keep_This;
class UseStatement
{
    public function run()
    {
        return new Keep_This;
    }
}
