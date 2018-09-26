<?php declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

$object = new Source\SomeModelType;

$config = $object->customMethodGetName();
$config = $object->customMethodGetName('key');

$object->customMethodSetName('key', 'value');
$object->customMethodSetName(['key' => 'value']);
