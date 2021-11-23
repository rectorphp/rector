<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector\Fixture;

use Baz\Bar;

function foo(Bar&\Qux $var)
{
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector\Fixture;

use Baz\Bar;

/**
 * @param \Baz\Bar&\Qux $var
 */
function foo($var)
{
}

?>
