<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class MightNotBeString
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

namespace Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class MightNotBeString
{
    public function run($value)
    {
        $empty = (string) $value === '';

        $empty = (string) $value === '';
    }
}

?>
