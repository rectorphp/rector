<?php declare(strict_types=1);

namespace Rector\NodeChanger;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;

final class MethodNameChanger
{
    /**
     * @param string[] $renameMethodMap
     * @param string[]|string[] $acceptedAssertionMethods
     */
    public function renameNode(
        MethodCall $node,
        array $renameMethodMap,
        array $acceptedAssertionMethods = [],
        ?string $activeFuncCallName = null
    ): void {
        $oldNodeMethodName = $node->name->toString();

        if (empty($acceptedAssertionMethods)) {
            $node->name = new Identifier($renameMethodMap[$oldNodeMethodName]);

            return;
        }

        [$trueMethodName, $falseMethodName] = $renameMethodMap[$activeFuncCallName];

        if (in_array($oldNodeMethodName, $acceptedAssertionMethods['trueMethodNames']) && $trueMethodName) {
            $node->name = new Identifier($trueMethodName);
        } elseif (in_array($oldNodeMethodName, $acceptedAssertionMethods['falseMethodNames']) && $falseMethodName) {
            $node->name = new Identifier($falseMethodName);
        }
    }
}
