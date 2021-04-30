<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDefineArrayConstantRector\Fixture;

define('ANIMALS', [
    'dog',
    'cat',
    'bird'
]);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeDefineArrayConstantRector\Fixture;

const ANIMALS = [
    'dog',
    'cat',
    'bird'
];

?>
