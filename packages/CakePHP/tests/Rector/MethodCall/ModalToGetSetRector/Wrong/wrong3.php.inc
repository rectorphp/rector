<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

function modalToGetSet3()
{
    $object = new Source\SomeModelType;

    $config = $object->method();
    $config = $object->method('key');

    $otherObject = 'var';
    $config = $object->$otherObject('key');
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

function modalToGetSet3()
{
    $object = new Source\SomeModelType;

    $config = $object->getMethod();
    $config = $object->setMethod('key');

    $otherObject = 'var';
    $config = $object->$otherObject('key');
}

?>
