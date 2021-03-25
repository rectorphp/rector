<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class EscapeContentDelimiterInPattern
{
    public function run($value)
    {
        preg_match('/^#(([a-f0-9]{3}){1,2})$/i', '#ffffff');
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class EscapeContentDelimiterInPattern
{
    public function run($value)
    {
        preg_match('#^\#(([a-f0-9]{3}){1,2})$#i', '#ffffff');
    }
}

?>
