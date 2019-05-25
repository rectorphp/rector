<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class SomeClass
{
    public function run()
    {
        preg_match('~value~', $value);
        preg_match_all('~value~im', $value);
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class SomeClass
{
    public function run()
    {
        preg_match('#value#', $value);
        preg_match_all('#value#im', $value);
    }
}

?>
