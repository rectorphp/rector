<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDefineArrayConstantRector\Fixture;

function skipInsideFunction()
{
    define('ANIMALS', [
        'dog',
        'cat',
        'bird'
    ]);
}

?>
