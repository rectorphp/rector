<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class EscapedDollarChar
{
    public function run($value)
    {
        preg_match("#^value\\$#", $value, $match);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class EscapedDollarChar
{
    public function run($value)
    {
        preg_match("#^value\\$#", $value, $match);
    }
}

?>
