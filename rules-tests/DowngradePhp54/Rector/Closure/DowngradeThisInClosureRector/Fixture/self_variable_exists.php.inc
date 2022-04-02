<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector\Fixture;

class SelfVariableExists
{
    public $property = 'test';

    public function run()
    {
        $self = 'test';
        $function = function () {
            echo $this->property;
        };

        $function();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector\Fixture;

class SelfVariableExists
{
    public $property = 'test';

    public function run()
    {
        $self = 'test';
        $self2 = $this;
        $function = function () use($self2) {
            echo $self2->property;
        };

        $function();
    }
}

?>
