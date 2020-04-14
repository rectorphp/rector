<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class SkipIfAssignNestedUse
{
    public function run($token)
    {
        if (($id = $this->get($token))) {
            pg_query_params('SELECT ... =$1', [
                $id,
            ]);
        }
    }
}
