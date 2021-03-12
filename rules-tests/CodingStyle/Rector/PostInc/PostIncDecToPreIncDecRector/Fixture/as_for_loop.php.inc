<?php

namespace Rector\CodingStyle\Tests\Rector\PostInc\PostIncDecToPreIncDecRector\Fixture;

class AsForLoop
{
    public function run($value = 1)
    {
        for ($i = 0; $i<10; $i++) {}
        for ($i = 0; $i<10; $i--) {}
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\PostInc\PostIncDecToPreIncDecRector\Fixture;

class AsForLoop
{
    public function run($value = 1)
    {
        for ($i = 0; $i<10; ++$i) {}
        for ($i = 0; $i<10; --$i) {}
    }
}

?>
