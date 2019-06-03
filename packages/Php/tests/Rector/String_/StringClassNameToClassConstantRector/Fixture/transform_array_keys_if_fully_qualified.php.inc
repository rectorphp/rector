<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class IgnoreArrayKeys
{
    public function run()
    {
        // This key contains the \Exception object
        return ['\Exception' => new \Exception()];
    }
}

?>
