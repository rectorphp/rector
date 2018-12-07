<?php

namespace Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\Fixture;

use Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\Source\TreeBuilder;

class SomeConfiguration2
{
    public function configure()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('override', 'array', new \stdClass());
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\Fixture;

use Rector\Symfony\Tests\Rector\New_\RootNodeTreeBuilderRector\Source\TreeBuilder;

class SomeConfiguration2
{
    public function configure()
    {
        $treeBuilder = new TreeBuilder('override', 'array', new \stdClass());
        $treeBuilder->getRootNode();
    }
}

?>
