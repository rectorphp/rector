<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Method;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;

final class DeprecationDetector extends NodeVisitorAbstract
{
    public function enterNode(Node $node): void
    {
        if (! $this->isTriggerErrorUserDeprecated($node)) {
            return;
        }

        /** @var FuncCall $funcCallNode */
        $funcCallNode = $node;

        $messageNode = $funcCallNode->args[0]->value;
        $message = '';
        if ($messageNode instanceof Concat) {
            $message .= $this->processConcatNode($messageNode->left);
            $message .= $this->processConcatNode($messageNode->right);
        }

        // @todo add to: deprecation collector
        // return $message;
    }

    private function isTriggerErrorUserDeprecated(Node $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        if (! $node->name instanceof Name) {
            return false;
        }

        if ($node->name->toString() !== 'trigger_error') {
            return false;
        }

        if (count($node->args) !== 2) {
            return false;
        }

        if (! $node->args[1]->value instanceof ConstFetch) {
            return false;
        }

        /** @var ConstFetch $constFetchNode */
        $constFetchNode = $node->args[1]->value;

        return $constFetchNode->name->toString() === 'E_USER_DEPRECATED';
    }

    private function processConcatNode(Node $node): string
    {
        if ($node instanceof Method) { // get method name in stirng, e.g. "getValue()"
            $classMethodNode = $this->findParentOfType($node, ClassMethod::class);

            return $classMethodNode->name->name;
        }

        if ($node instanceof String_) {
            return $node->value;
        }

        // @todo implement
    }

    private function findParentOfType(Node $node, string $type): ClassMethod
    {
        $parentNode = $node->getAttribute('parent');

        while (! is_a($parentNode , $type, true)) {
            $parentNode = $parentNode->getAttribute('parent');
        }

        return $parentNode;
    }
}
