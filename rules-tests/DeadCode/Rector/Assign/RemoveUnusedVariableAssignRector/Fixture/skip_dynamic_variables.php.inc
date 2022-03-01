<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class SkipDynamicVariable
{
    public function run($something)
    {
         foreach($something as $key => $mapped)
         {
             ${$key . "_key"} = $mapped;
         }
    }
}
