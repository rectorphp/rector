<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class IgnoreArrayKeys
{
    public function run()
    {
        // Imagine we want to return the kind of error we received: in
        // this case, an exception, but not a real \Exception object,
        // only a string.
        return 'exception';
    }
}

?>
