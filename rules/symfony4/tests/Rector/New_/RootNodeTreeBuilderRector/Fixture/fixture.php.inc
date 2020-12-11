<?php

namespace Rector\Symfony4\Tests\Rector\New_\RootNodeTreeBuilderRector\Fixture;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class SomeConfiguration
{
    public function configure()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('acme_root');
        $rootNode->someCall();

        $treeBuilder = new TreeBuilder('already_root');
        $rootNode = $treeBuilder->root();
        $rootNode->someCall();
    }
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\New_\RootNodeTreeBuilderRector\Fixture;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class SomeConfiguration
{
    public function configure()
    {
        $treeBuilder = new TreeBuilder('acme_root');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode->someCall();

        $treeBuilder = new TreeBuilder('already_root');
        $rootNode = $treeBuilder->root();
        $rootNode->someCall();
    }
}

?>
