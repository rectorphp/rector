<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

final class InlineAssignOp
{
    public function run()
    {
        $value = null;
        is_string($value) && $value .= 'append';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

final class InlineAssignOp
{
    public function run()
    {
        $value = null;
        if (is_string($value)) {
            $value .= 'append';
        }
    }
}

?>
