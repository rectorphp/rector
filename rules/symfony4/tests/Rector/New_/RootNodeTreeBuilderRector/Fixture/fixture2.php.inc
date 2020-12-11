<?php

namespace Rector\Symfony4\Tests\Rector\New_\RootNodeTreeBuilderRector\Fixture;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

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

namespace Rector\Symfony4\Tests\Rector\New_\RootNodeTreeBuilderRector\Fixture;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class SomeConfiguration2
{
    public function configure()
    {
        $treeBuilder = new TreeBuilder('override', 'array', new \stdClass());
        $treeBuilder->getRootNode();
    }
}

?>
