<?php declare(strict_types=1);

namespace Rector\TriggerExtractor\TriggerError;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\MagicConst\Method;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;

final class TriggerMessageResolver
{
    public function resolve(Node $node): string
    {
        $message = '';
        if ($node instanceof Concat) {
            $message .= $this->processConcatNode($node->left);
            $message .= $this->processConcatNode($node->right);
        }

        return $message;
    }

    private function processConcatNode(Node $node): string
    {
        if ($node instanceof Method) {
            $classMethodNode = $this->findParentOfType($node, ClassMethod::class);

            return $classMethodNode->name->name;
        }

        if ($node instanceof String_) {
            return $node->value;
        }

        throw new Exception(sprintf(
            'Not implemented yet. Go to "%s::%s()" and add check for "%s" node.',
            __CLASS__,
            __METHOD__,
            get_class($node)
        ));
    }

    private function findParentOfType(Node $node, string $type): Node
    {
        $parentNode = $node->getAttribute('parent');

        while (! is_a($parentNode, $type, true)) {
            $parentNode = $parentNode->getAttribute('parent');
        }

        return $parentNode;
    }
}
