<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;

function renameMethodCallBasedOnParameter()
{
    $object = new Source\SomeModelType;

    $config = $object->getParam('paging');
    $object->withParam('paging', 'value');

    $config = $object->getParam($value);

    $config = $object->getParam('other');
    $object->withParam('other', 'value');
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\MethodCall\RenameMethodCallBasedOnParameterRector;

function renameMethodCallBasedOnParameter()
{
    $object = new Source\SomeModelType;

    $config = $object->getAttribute('paging');
    $object->withAttribute('paging', 'value');

    $config = $object->getParam($value);

    $config = $object->getParam('other');
    $object->withParam('other', 'value');
}

?>
