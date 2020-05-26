<?php

use Rector\Core\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Source\Keep_This;

class Rector_Core_Tests_Rector_Namespace__PseudoNamespaceToNamespaceRector_Fixture_UseStatement
{
    public function run()
    {
        return new Keep_This;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Fixture;

use Rector\Core\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Source\Keep_This;

class UseStatement
{
    public function run()
    {
        return new Keep_This;
    }
}

?>
