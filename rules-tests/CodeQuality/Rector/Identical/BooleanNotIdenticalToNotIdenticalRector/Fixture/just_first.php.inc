<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector\Fixture;

class JustFirst
{
    public function run()
    {
        $a = 'true';
        $b = false;

        var_dump(! $a === $b); // true
        var_dump(! ($a === $b)); // true
        var_dump($a !== $b); // true
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector\Fixture;

class JustFirst
{
    public function run()
    {
        $a = 'true';
        $b = false;

        var_dump($a !== $b); // true
        var_dump(! ($a === $b)); // true
        var_dump($a !== $b); // true
    }
}

?>
