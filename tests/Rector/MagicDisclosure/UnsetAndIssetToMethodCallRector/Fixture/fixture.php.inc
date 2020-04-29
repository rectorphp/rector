<?php

namespace Rector\Core\Tests\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector\Fixture;

class SomeClass
{
    function unsetAndIsset()
    {
        $container = new \Rector\Core\Tests\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector\Source\LocalContainer;
        isset($container['someService']);
        unset($container['someService']);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector\Fixture;

class SomeClass
{
    function unsetAndIsset()
    {
        $container = new \Rector\Core\Tests\Rector\MagicDisclosure\UnsetAndIssetToMethodCallRector\Source\LocalContainer;
        $container->hasService('someService');
        $container->removeService('someService');
    }
}

?>
