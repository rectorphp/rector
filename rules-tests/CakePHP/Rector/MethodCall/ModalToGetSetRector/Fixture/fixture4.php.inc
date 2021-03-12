<?php

namespace Rector\Tests\CakePHP\Rector\MethodCall\ModalToGetSetRector\Fixture;

use Rector\Tests\CakePHP\Rector\MethodCall\ModalToGetSetRector\Source;

function modalToGetSet4()
{
    $object = new Source\SomeModelType;

    $object->makeEntity();
}

?>
-----
<?php

namespace Rector\Tests\CakePHP\Rector\MethodCall\ModalToGetSetRector\Fixture;

use Rector\Tests\CakePHP\Rector\MethodCall\ModalToGetSetRector\Source;

function modalToGetSet4()
{
    $object = new Source\SomeModelType;

    $object->createEntity();
}

?>
