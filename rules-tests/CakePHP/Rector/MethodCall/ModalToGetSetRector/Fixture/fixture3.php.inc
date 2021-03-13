<?php

namespace Rector\Tests\CakePHP\Rector\MethodCall\ModalToGetSetRector\Fixture;

use Rector\Tests\CakePHP\Rector\MethodCall\ModalToGetSetRector\Source;

function modalToGetSet3()
{
    $object = new Source\SomeModelType;

    $config = $object->method();
    $config = $object->method('key');
}

?>
-----
<?php

namespace Rector\Tests\CakePHP\Rector\MethodCall\ModalToGetSetRector\Fixture;

use Rector\Tests\CakePHP\Rector\MethodCall\ModalToGetSetRector\Source;

function modalToGetSet3()
{
    $object = new Source\SomeModelType;

    $config = $object->getMethod();
    $config = $object->setMethod('key');
}

?>
