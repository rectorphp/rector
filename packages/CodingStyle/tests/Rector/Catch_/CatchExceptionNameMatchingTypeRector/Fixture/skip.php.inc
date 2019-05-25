<?php

namespace Rector\CodingStyle\Tests\Rector\Catch_\CatchExceptionNameMatchingTypeRector\Fixture;

class Skip
{
    public function run()
    {
        try {
            // ...
        } catch (CorrectException $correctException) {
            $correctException->getMessage();
        }
    }
}
