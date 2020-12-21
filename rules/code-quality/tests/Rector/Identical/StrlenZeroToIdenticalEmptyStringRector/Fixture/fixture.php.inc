<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class Fixture
{
    public function run($value)
    {
        $empty = strlen($value) === 0;

        $empty = 0 === strlen($value);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class Fixture
{
    public function run($value)
    {
        $empty = $value === '';

        $empty = $value === '';
    }
}

?>
