<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Fixture;

use Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Source;

function arrayToFluentCall()
{
    $factory = new Source\FactoryClass();

    $factory->buildClass('foo');

    $factory->buildClass('foo', []);

    $factory->buildClass('foo', ['baz' => 1]);
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Fixture;

use Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Source;

function arrayToFluentCall()
{
    $factory = new Source\FactoryClass();

    $factory->buildClass('foo');

    $factory->buildClass('foo');

    $factory->buildClass('foo', ['baz' => 1]);
}

?>
