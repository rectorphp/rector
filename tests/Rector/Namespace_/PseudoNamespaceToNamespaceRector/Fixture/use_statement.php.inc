<?php

use Rector\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Source\Keep_This;

class Rector_Tests_Rector_Namespace__PseudoNamespaceToNamespaceRector_Fixture_UseStatement
{
    public function run()
    {
        return new Keep_This;
    }
}

?>
-----
<?php


namespace Rector\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Fixture;

use Rector\Tests\Rector\Namespace_\PseudoNamespaceToNamespaceRector\Source\Keep_This;

class UseStatement
{
    public function run()
    {
        return new Keep_This;
    }
}

?>
