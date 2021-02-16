<?php

namespace Rector\CodeQuality\Tests\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

class AssignOpOr
{
    public function run()
    {
        $value = null;
        is_string($value) || $value .= 'append';
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

class AssignOpOr
{
    public function run()
    {
        $value = null;
        if (!is_string($value)) {
            $value .= 'append';
        }
    }
}

?>
