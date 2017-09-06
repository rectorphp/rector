<?php declare(strict_types=1);

namespace Rector\TriggerExtractor;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\MagicConst\Method;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\NotImplementedException;

final class TriggerMessageResolver
{
    /**
     * Probably resolve by recursion, similar too
     * @see \Rector\NodeTypeResolver\NodeVisitor\TypeResolver::__construct()
     */
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

            return $node->getAttribute('class') . '::' . $classMethodNode->name->name;
        }

        if ($node instanceof String_) {
            $message = $node->value; // complet class to local methods
            return $this->completeClassToLocalMethods($message, $node->getAttribute('class'));
        }

        throw new NotImplementedException(sprintf(
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

    private function completeClassToLocalMethods(string $message, string $class): string
    {
        $completeMessage = '';
        $words = explode(' ', $message);

        foreach ($words as $word) {
            // is method()
            if (Strings::endsWith($word, '()') && strlen($word) > 2) {
                // doesn't include class in the beggning
                if (! Strings::startsWith($word, $class)) {
                    $word = $class . '::' . $word;
                }
            }

            $completeMessage .= ' ' . $word;
        }

        return trim($completeMessage);
    }
}
