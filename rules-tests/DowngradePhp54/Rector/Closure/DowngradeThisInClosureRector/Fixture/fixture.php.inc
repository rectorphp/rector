<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector\Fixture;

class Fixture
{
    public $property = 'test';

    public function run()
    {
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

class Fixture
{
    public $property = 'test';

    public function run()
    {
        $self = $this;
        $function = function () use($self) {
            echo $self->property;
        };

        $function();
    }
}

?>
