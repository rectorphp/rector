<?php

namespace Rector\CodeQuality\Tests\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

class Fixture
{
    public function run()
    {
        $userId = null;

        is_null($userId) && $userId = 5;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Expression\InlineIfToExplicitIfRector\Fixture;

class Fixture
{
    public function run()
    {
        $userId = null;

        if (is_null($userId)) {
            $userId = 5;
        }
    }
}

?>
