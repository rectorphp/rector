<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class DefinitelyString
{
    public function run(string $value)
    {
        $empty = strlen($value) === 0;

        $empty = 0 === strlen($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class DefinitelyString
{
    public function run(string $value)
    {
        $empty = $value === '';

        $empty = $value === '';
    }
}

?>
