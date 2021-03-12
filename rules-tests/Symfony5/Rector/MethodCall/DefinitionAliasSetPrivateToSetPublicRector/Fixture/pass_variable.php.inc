<?php

namespace Rector\Tests\Symfony5\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector\Fixture;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Definition;

class PassVariable
{
    public function run()
    {
        $var = false;
        $definition = new Definition('Example\Foo');
        $definition->setPrivate($var);

        $alias = new Alias('Example\Foo');
        $alias->setPrivate($var);
    }
}
-----
<?php

namespace Rector\Tests\Symfony5\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector\Fixture;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Definition;

class PassVariable
{
    public function run()
    {
        $var = false;
        $definition = new Definition('Example\Foo');
        $definition->setPublic(!$var);

        $alias = new Alias('Example\Foo');
        $alias->setPublic(!$var);
    }
}
