<?php

namespace Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

use Exception;

class SkipVariablePrevDefined
{
    public function run()
    {
        $exception = "";

        try {
            // something
        } catch (Exception $ex) {
            // do something
        }
    }
}

?>
