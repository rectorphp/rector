<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\RemoveSoleValueSprintfRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = sprintf('%s', 'hi');

        $welcome = 'hello';
        $value = sprintf('%s', $welcome);
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\RemoveSoleValueSprintfRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = 'hi';

        $welcome = 'hello';
        $value = $welcome;
    }
}

?>
