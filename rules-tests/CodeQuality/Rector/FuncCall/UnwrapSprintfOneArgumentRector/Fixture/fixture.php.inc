<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector\Fixture;

class Fixture
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

namespace Rector\Tests\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector\Fixture;

class Fixture
{
    public function run($var)
    {
        echo 'value';
        echo $var;
    }
}

?>
