<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\DeadCode\ValueObject\VariableNodeUse;

class KeepNullableTwo
{
    private function isAssignNodeUsed(?VariableNodeUse $previousNode, VariableNodeUse $nodeByTypeAndPosition): bool
    {
    }
}
