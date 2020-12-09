<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\UnwrapSprintfOneArgumentRector\Fixture;

class SomeClass
{
    public function run($var)
    {
        echo sprintf('value');
        echo sprintf($var);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\UnwrapSprintfOneArgumentRector\Fixture;

class SomeClass
{
    public function run($var)
    {
        echo 'value';
        echo $var;
    }
}

?>
