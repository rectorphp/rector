<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

function modalToGetSet()
{
    $object = new Source\SomeModelType;

    $config = $object->config();
    $config = $object->config('key');
    $object->config('key', 'value');
    $object->config(['key' => 'value']);
    $object->config('key');
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

function modalToGetSet()
{
    $object = new Source\SomeModelType;

    $config = $object->getConfig();
    $config = $object->getConfig('key');
    $object->setConfig('key', 'value');
    $object->setConfig(['key' => 'value']);
    $object->getConfig('key');
}

?>
