<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

function run(): never
{
    echo 'foo';
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\Fixture;

/**
 * @return never
 */
function run()
{
    echo 'foo';
}

?>
