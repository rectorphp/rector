<?php

namespace Rector\Tests\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector\Fixture;

define('ANIMALS', [
    'dog',
    'cat',
    'bird'
]);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector\Fixture;

const ANIMALS = [
    'dog',
    'cat',
    'bird'
];

?>
