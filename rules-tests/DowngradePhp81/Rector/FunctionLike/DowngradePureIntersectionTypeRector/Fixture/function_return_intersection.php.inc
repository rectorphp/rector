<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector\Fixture;

use Baz\Bar;

function foo(): Bar&\Qux
{
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector\Fixture;

use Baz\Bar;

/**
 * @return \Baz\Bar&\Qux
 */
function foo()
{
}

?>
