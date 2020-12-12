<?php

use Rector\Generic\Tests\Rector\Name\PseudoNamespaceToNamespaceRector\Source\Keep_This;

class UseStatement
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

use Rector\Generic\Tests\Rector\Name\PseudoNamespaceToNamespaceRector\Source\Keep_This;
class UseStatement
{
    public function run()
    {
        return new Keep_This;
    }
}
