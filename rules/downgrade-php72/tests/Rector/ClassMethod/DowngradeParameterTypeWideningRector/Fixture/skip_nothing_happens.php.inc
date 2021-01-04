<?php

namespace Rector\DowngradePhp72\Tests\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface NothingHappensInterface
{
    public function test(array $input);
}

class SkipNothingHappens implements NothingHappensInterface
{
    public function test(array $input)
    {
        /* ... */
    }
}

?>
