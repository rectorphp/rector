<?php

namespace Rector\Tests\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector\Fixture;

class AsArrayKey
{
    public function run($value = 1)
    {
        $x[$value++];
        $x[$value--];
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector\Fixture;

class AsArrayKey
{
    public function run($value = 1)
    {
        $x[$value];
        ++$value;
        $x[$value];
        --$value;
    }
}

?>
