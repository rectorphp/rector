<?php declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

$object = new Source\SomeModelType;

$config = $object->customMethod();
$config = $object->customMethod('key');

$object->customMethod('key', 'value');
$object->customMethod(['key' => 'value']);
