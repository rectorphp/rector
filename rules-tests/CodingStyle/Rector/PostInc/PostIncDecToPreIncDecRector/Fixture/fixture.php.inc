<?php

namespace Rector\CodingStyle\Tests\Rector\PostInc\PostIncDecToPreIncDecRector\Fixture;

class Fixture
{
    public function run($value = 1)
    {
        $value++; echo $value;
        $value--; echo $value;
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\PostInc\PostIncDecToPreIncDecRector\Fixture;

class Fixture
{
    public function run($value = 1)
    {
        ++$value; echo $value;
        --$value; echo $value;
    }
}

?>
