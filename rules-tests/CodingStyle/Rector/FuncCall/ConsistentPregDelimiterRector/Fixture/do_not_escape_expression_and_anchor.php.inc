<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class DoNotEscapedExpressionAndAnchor
{
    public function run($value)
    {
        preg_match("/^value$/", $value);
        preg_match("/^[-. '\p{L}]+$/u", $value);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class DoNotEscapedExpressionAndAnchor
{
    public function run($value)
    {
        preg_match("#^value$#", $value);
        preg_match("#^[-. '\p{L}]+$#u", $value);
    }
}

?>
