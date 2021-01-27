<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector\Fixture;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Definition;

class Fixture
{
    public function run()
    {
        $definition = new Definition('Example\Foo');
        $definition->setPrivate(false);
        $definition->setPrivate(true);

        $alias = new Alias('Example\Foo');
        $alias->setPrivate(false);
        $alias->setPrivate(true);
    }
}
-----
<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector\Fixture;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Definition;

class Fixture
{
    public function run()
    {
        $definition = new Definition('Example\Foo');
        $definition->setPublic(true);
        $definition->setPublic(false);

        $alias = new Alias('Example\Foo');
        $alias->setPublic(true);
        $alias->setPublic(false);
    }
}