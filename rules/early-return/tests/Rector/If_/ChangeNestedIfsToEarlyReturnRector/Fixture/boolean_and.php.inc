<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\Fixture;

use Rector\NodeTypeResolver\Node\AttributeKey;

class BooleanAnd
{
    private function isPreviousExpressionVisuallySimilar(Expression $previousExpression, Node $previousNode): bool
    {
        $prePreviousExpression = $previousExpression->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        if ($prePreviousExpression instanceof Expression && $prePreviousExpression->expr instanceof AssignOp) {
            if ($this->areNodesEqual($prePreviousExpression->expr->var, $previousNode->var)) {
                return true;
            }
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\Fixture;

use Rector\NodeTypeResolver\Node\AttributeKey;

class BooleanAnd
{
    private function isPreviousExpressionVisuallySimilar(Expression $previousExpression, Node $previousNode): bool
    {
        $prePreviousExpression = $previousExpression->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        if (!$prePreviousExpression instanceof Expression) {
            return false;
        }
        if (!$prePreviousExpression->expr instanceof AssignOp) {
            return false;
        }
        if ($this->areNodesEqual($prePreviousExpression->expr->var, $previousNode->var)) {
            return true;
        }

        return false;
    }
}

?>
