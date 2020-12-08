<?php

namespace Rector\DowngradePhp72\Tests\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface NothingHappensInterface
{
    public function test(array $input);
}

class NothingHappens implements NothingHappensInterface
{
    public function test(array $input)
    {
        /* ... */
    }
}

?>
