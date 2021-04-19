<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class UpdateMultilinePcreExtendedPattern
{
    public function run()
    {
        $file = preg_replace('/^(
            test
        )/x', '', $file);

        $file = preg_replace('#^(
            test
        )#x', '', $file);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

class UpdateMultilinePcreExtendedPattern
{
    public function run()
    {
        $file = preg_replace('#^(
            test
        )#x', '', $file);

        $file = preg_replace('#^(
            test
        )#x', '', $file);
    }
}

?>
