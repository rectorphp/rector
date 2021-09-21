<?php

namespace Rector\Tests\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector\FixtureSkipped;

final class Fixture
{
    public function run()
    {
        try {
        } catch (Throwable $notUsedThrowable) {
        }
    }
}

?>
