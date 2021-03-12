<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\DeadCode\ValueObject\VariableNodeUse;

class KeepNullableTwo
{
    private function isAssignNodeUsed(?VariableNodeUse $previousNode, VariableNodeUse $nodeByTypeAndPosition): bool
    {
    }
}
