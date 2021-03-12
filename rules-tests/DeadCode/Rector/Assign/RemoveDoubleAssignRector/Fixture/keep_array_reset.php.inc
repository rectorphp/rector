<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleAssignRector\Fixture;

class KeepArrayReset
{
    public function create()
    {
        $values = [];
        $unusedParameters = [];

        foreach ($values as $param) {
            if (true) {
                // reset
                $unusedParameters = [];

                continue;
            }

            $unusedParameters[] = $param;
        }

        return $unusedParameters;
    }
}
