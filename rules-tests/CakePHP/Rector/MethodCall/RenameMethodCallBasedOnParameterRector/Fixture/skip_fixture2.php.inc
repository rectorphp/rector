<?php

namespace Rector\Tests\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\Fixture;

use Rector\Tests\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\Source;

function renameMethodCallBasedOnParameterNoop()
{
    $object = new Source\SomeModelType;

    $config = $object->getParam($value);
    $config = $object->getParam('other');
    $object->withParam('other', 'value');
}
