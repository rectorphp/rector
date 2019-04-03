<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector\Fixture;

class Keep
{
    public function run()
    {
        $a = 'true';
        $b = false;

        var_dump(! ($a === $b)); // true
        var_dump($a !== $b); // true
    }

    public function again()
    {
        $a = true;
        $b = 'false';

        var_dump(! $a === $b); // true
        var_dump(! ($a === $b)); // true
        var_dump($a !== $b); // true
    }
}
