<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class SkipAssignMethodCall
{
    private function get()
    {
        session_start();

        return rand(0, 1);
    }

    public function run()
    {
        $value = $this->get();
        $value = 1;
    }
}

?>
