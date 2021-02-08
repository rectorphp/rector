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
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Identical\GetClassToInstanceOfRector\GetClassToInstanceOfRectorTest
 */
final class GetClassToInstanceOfRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const NO_NAMESPACED_CLASSNAMES = ['self', 'static'];

    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
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

        if (! $twoNodeMatch instanceof TwoNodeMatch) {
            return null;
        }

        /** @var ClassConstFetch|String_ $firstExpr */
        $firstExpr = $twoNodeMatch->getFirstExpr();

        /** @var FuncCall $funcCall */
        $funcCall = $twoNodeMatch->getSecondExpr();

        $varNode = $funcCall->args[0]->value;

        if ($firstExpr instanceof String_) {
            $className = $this->valueResolver->getValue($firstExpr);
        } else {
            $className = $this->getName($firstExpr->class);
        }

        if ($className === null) {
            return null;
        }

        $class = in_array($className, self::NO_NAMESPACED_CLASSNAMES, true)
            ? new Name($className)
            : new FullyQualified($className);
        $instanceof = new Instanceof_($varNode, $class);
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
}
