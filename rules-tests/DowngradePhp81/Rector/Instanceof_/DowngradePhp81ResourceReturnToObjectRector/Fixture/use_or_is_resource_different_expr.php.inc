<?php

namespace Rector\Tests\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector\Fixture;

class UseOrIsResourceDifferentExpr
{
    public function run($obj, $expr)
    {
        $obj instanceof \finfo && is_resource($expr);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\Instanceof_\DowngradePhp81ResourceReturnToObjectRector\Fixture;

class UseOrIsResourceDifferentExpr
{
    public function run($obj, $expr)
    {
        (is_resource($obj) || $obj instanceof \finfo) && is_resource($expr);
    }
}

?>
