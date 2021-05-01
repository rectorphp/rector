<?php

namespace Rector\Tests\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector\Fixture;

function skipNotInExpression()
{
    a_func_call(
        define('ANIMALS', [
            'dog',
            'cat',
            'bird'
        ])
    );
}

?>
