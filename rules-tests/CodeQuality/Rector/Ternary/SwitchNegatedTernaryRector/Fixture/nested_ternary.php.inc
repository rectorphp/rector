<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector\Fixture;

class NestedTernary
{
    function run($result)
    {
        return ! isset($result['validation_messages'])
            ? []
            : (isset($result['validation_messages']['http']) ? $result['validation_messages'] : $result);
    }

    function run2($a, $b)
    {
        return ! isset($a)
            ? (isset($b) ? $b : 'b')
            : $a;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector\Fixture;

class NestedTernary
{
    function run($result)
    {
        return isset($result['validation_messages'])
            ? (isset($result['validation_messages']['http']) ? $result['validation_messages'] : $result)
            : ([]);
    }

    function run2($a, $b)
    {
        return isset($a)
            ? ($a)
            : (isset($b) ? $b : 'b');
    }
}

?>
