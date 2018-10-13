<?php declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ModalToGetSetRector;

$object = new Source\SomeModelType;

$config = $object->getMethod();
$config = $object->setMethod('key');

$otherObject = 'var';
$config = $object->$otherObject('key');
