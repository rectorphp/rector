<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector\Fixture;

use Baz\Bar;

function foo(Bar&\Qux $var): Bar&\Qux
{
    return $var;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector\Fixture;

use Baz\Bar;

/**
 * @param \Baz\Bar&\Qux $var
 * @return \Baz\Bar&\Qux
 */
function foo($var)
{
    return $var;
}

?>
