<?php

namespace Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\Rector;

class TypedParam
{
    public function run(bool $isStrict = true)
    {
        $value = (bool) $isStrict;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Cast\RecastingRemovalRector\Rector;

class TypedParam
{
    public function run(bool $isStrict = true)
    {
        $value = $isStrict;
    }
}

?>
