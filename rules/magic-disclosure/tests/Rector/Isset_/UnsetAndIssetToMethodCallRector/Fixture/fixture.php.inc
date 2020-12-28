<?php

namespace Rector\MagicDisclosure\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector\Fixture;

class Fixture
{
    function unsetAndIsset()
    {
        $container = new \Rector\MagicDisclosure\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector\Source\LocalContainer;
        isset($container['someService']);
        unset($container['someService']);
    }
}

?>
-----
<?php

namespace Rector\MagicDisclosure\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector\Fixture;

class Fixture
{
    function unsetAndIsset()
    {
        $container = new \Rector\MagicDisclosure\Tests\Rector\Isset_\UnsetAndIssetToMethodCallRector\Source\LocalContainer;
        $container->hasService('someService');
        $container->removeService('someService');
    }
}

?>
