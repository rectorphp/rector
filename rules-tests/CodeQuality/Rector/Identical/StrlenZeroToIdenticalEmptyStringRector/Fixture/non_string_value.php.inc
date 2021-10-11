<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class NonStringValue
{
    public function run()
    {
        $value = null;

        $empty = strlen($value) === 0;

        $empty = 0 === strlen($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector\Fixture;

class NonStringValue
{
    public function run()
    {
        $value = null;

        $empty = (string) $value === '';

        $empty = (string) $value === '';
    }
}

?>
