<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Fixture;

function modalToGetSet4()
{
    $object = new Source\SomeModelType;

    $object->makeEntity();
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector\Fixture;

function modalToGetSet4()
{
    $object = new Source\SomeModelType;

    $object->createEntity();
}

?>
