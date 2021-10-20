<?php

namespace Rector\Tests\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector\Fixture;

final class SkipWhenVariableIsUsedOutsideCatch
{
    public function run()
    {
        $exception = null;

        try {
            DateTime::createFromFormat('u', 234234);
        } catch (\Exception $exception) {
            // Store in $exception
        }

        // Do other things

        if ($exception !== null) {
            // Handle exception cleanup
        }
    }
}
?>

