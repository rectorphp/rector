<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Fixture;

function modalToGetSet3()
{
    $object = new Source\SomeModelType;

    $config = $object->method();
    $config = $object->method('key');
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Fixture;

function modalToGetSet3()
{
    $object = new Source\SomeModelType;

    $config = $object->getMethod();
    $config = $object->setMethod('key');
}

?>
