<?php

declare(strict_types=1);

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
use Rector\Core\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector\GetClassToInstanceOfRectorTest
 */
final class GetClassToInstanceOfRector extends AbstractRector
{
    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
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
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode(
            $node,
            function (Node $node): bool {
                return $this->isClassReference($node);
            },
            function (Node $node): bool {
                return $this->isGetClassFuncCallNode($node);
            }
        );

        if ($twoNodeMatch === null) {
            return null;
        }

        /** @var ClassConstFetch $classReferenceNode */
        $classReferenceNode = $twoNodeMatch->getFirstExpr();
        /** @var FuncCall $funcCallNode */
        $funcCallNode = $twoNodeMatch->getSecondExpr();

        $varNode = $funcCallNode->args[0]->value;
        $className = $this->matchClassName($classReferenceNode);
        if ($className === null) {
            return null;
        }

        $instanceof = new Instanceof_($varNode, new FullyQualified($className));
        if ($node instanceof NotIdentical) {
            return new BooleanNot($instanceof);
        }

        return $instanceof;
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
