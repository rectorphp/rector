<?php

namespace Rector\Tests\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\Fixture;

use Rector\Tests\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\Source;

function renameMethodCallBasedOnParameter()
{
    $object = new Source\SomeModelType;

    $config = $object->getParam('paging');
    $object->withParam('paging', 'value');
}

?>
-----
<?php

namespace Rector\Tests\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\Fixture;

use Rector\Tests\CakePHP\Rector\MethodCall\RenameMethodCallBasedOnParameterRector\Source;

function renameMethodCallBasedOnParameter()
{
    $object = new Source\SomeModelType;

    $config = $object->getAttribute('paging');
    $object->withAttribute('paging', 'value');
}

?>
