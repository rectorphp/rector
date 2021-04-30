<?php

namespace Rector\Tests\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector\Fixture;

function skipInsideFunction()
{
    define('ANIMALS', [
        'dog',
        'cat',
        'bird'
    ]);
}

?>
