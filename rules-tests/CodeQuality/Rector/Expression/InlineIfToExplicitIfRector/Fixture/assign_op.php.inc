<?php

namespace Rector\Tests\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

class AssignOp
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

class AssignOp
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
