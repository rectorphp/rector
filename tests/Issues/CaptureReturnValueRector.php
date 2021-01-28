<?php
declare(strict_types=1);

namespace Rector\Core\Tests\Issues;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class CaptureReturnValueRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @var MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $assignReturnValue = new Assign($node->var, $node);
        if ($node->getAttribute(AttributeKey::PARENT_NODE) instanceof Assign) {
            /*
             * This should prevent the infinite loop, but if the Assign node
             * is the one we've just created, it won't have the parent attribute yet.
             */

            return null;
        }

        /*
         * So we need to do add this, but it would be nice if Rector would do this for us,
         * maybe by running the node connecting visitor again on the returned node.
         */
//        $node->setAttribute(AttributeKey::PARENT_NODE, $assignReturnValue);

        return $assignReturnValue;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('', []);
    }
}
