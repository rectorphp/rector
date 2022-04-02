<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector\Fixture;

class DeepClosure
{
    public $property = 'test';

    public function run()
    {
        $function = function () {
            $f = function () {
                echo $this->property;
            };
            $f();
        };

        $function();
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\Closure\DowngradeThisInClosureRector\Fixture;

class DeepClosure
{
    public $property = 'test';

    public function run()
    {
        $self = $this;
        $function = function () use($self) {
            $f = function () use($self) {
                echo $self->property;
            };
            $f();
        };

        $function();
    }
}

?>
