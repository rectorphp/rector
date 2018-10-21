<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class GetClassToInstanceOfRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes comparison with get_class to instanceof',
            [
                new CodeSample(
                    'if (EventsListener::class === get_class($event->job)) { }',
                    'if ($event->job instanceof EventsListener) { }'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->isClassReference($node->left) && $this->isGetClassFuncCallNode($node->right)) {
            /** @var FuncCall $funcCallNode */
            $funcCallNode = $node->right;
            $varNode = $funcCallNode->args[0]->value;

            $className = $this->matchClassName($node->left);
        } elseif ($this->isClassReference($node->right) && $this->isGetClassFuncCallNode($node->left)) {
            /** @var FuncCall $funcCallNode */
            $funcCallNode = $node->left;
            $varNode = $funcCallNode->args[0]->value;

            $className = $this->matchClassName($node->right);
        } else {
            return null;
        }

        $instanceOfNode = new Instanceof_($varNode, new FullyQualified($className));
        if ($node instanceof NotIdentical) {
            return new BooleanNot($instanceOfNode);
        }

        return $instanceOfNode;
    }

    private function isClassReference(Node $node): bool
    {
        if ($node instanceof ClassConstFetch && (string) $node->name === 'class') {
            return true;
        }

        // might be
        return $node instanceof String_;
    }

    private function isGetClassFuncCallNode(Node $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        return $this->isName($node, 'get_class');
    }

    private function matchClassName(Node $node): ?string
    {
        if ($node instanceof ClassConstFetch) {
            return (string) $node->class;
        }

        if ($node instanceof String_) {
            return $node->value;
        }

        return null;
    }
}
