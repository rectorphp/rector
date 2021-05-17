<?php

namespace Rector\Tests\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector\Fixture;

class SomeClass
{
    public function run()
    {
        some_function(true);
    }
}

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector\Fixture;

class SomeClass
{
    public function run()
    {
        some_function(\Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE);
    }
}

?>
