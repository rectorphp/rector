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
use Rector\PhpParser\Node\Maintainer\BinaryOpMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class GetClassToInstanceOfRector extends AbstractRector
{
    /**
     * @var BinaryOpMaintainer
     */
    private $binaryOpMaintainer;

    public function __construct(BinaryOpMaintainer $binaryOpMaintainer)
    {
        $this->binaryOpMaintainer = $binaryOpMaintainer;
    }

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
        $matchedNodes = $this->binaryOpMaintainer->matchFirstAndSecondConditionNode(
            $node,
            function (Node $node) {
                return $this->isClassReference($node);
            },
            function (Node $node) {
                return $this->isGetClassFuncCallNode($node);
            }
        );

        if ($matchedNodes === null) {
            return null;
        }

        /** @var ClassConstFetch $classReferenceNode */
        /** @var FuncCall $funcCallNode */
        [$classReferenceNode, $funcCallNode] = $matchedNodes;

        $varNode = $funcCallNode->args[0]->value;
        $className = $this->matchClassName($classReferenceNode);
        if ($className === null) {
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
        if ($node instanceof ClassConstFetch && $this->isName($node->name, 'class')) {
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
            return $this->getName($node->class);
        }

        if ($node instanceof String_) {
            return $node->value;
        }

        return null;
    }
}
