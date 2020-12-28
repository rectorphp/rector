<?php

namespace Rector\Php74\Tests\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class Fixture
{
    public function run($meetups)
    {
        return array_filter($meetups, function (Meetup $meetup) {
            return is_object($meetup);
        });
    }
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Closure\ClosureToArrowFunctionRector\Fixture;

class Fixture
{
    public function run($meetups)
    {
        return array_filter($meetups, fn(Meetup $meetup) => is_object($meetup));
    }
}

?>
