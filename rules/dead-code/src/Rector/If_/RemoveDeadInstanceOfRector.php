<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\If_\RemoveDeadInstanceOfRector\RemoveDeadInstanceOfRectorTest
 */
final class RemoveDeadInstanceOfRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove dead instanceof check on type hinted variable', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go(stdClass $stdClass)
    {
        if (! $stdClass instanceof stdClass) {
            return false;
        }
        return true;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go(stdClass $stdClass)
    {
        return true;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->ifManipulator->isIfWithoutElseAndElseIfs($node)) {
            return null;
        }

        if ($node->cond instanceof BooleanNot && $node->cond->expr instanceof Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond->expr);
        }

        if ($node->cond instanceof Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond);
        }

        return $node;
    }

    private function processMayDeadInstanceOf(If_ $if, Instanceof_ $instanceof): ?Node
    {
        $previousVar = $this->betterNodeFinder->findFirstPrevious($if, function (Node $node) use ($instanceof): bool {
            return $node !== $instanceof->expr && $this->areNodesEqual($node, $instanceof->expr);
        });

        if (! $previousVar instanceof Node) {
            return null;
        }

        $phpDocInfo = $previousVar->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        $name = $this->getName($instanceof->class);
        if ($name === null) {
            return null;
        }

        $objectType = $this->getObjectType($previousVar);
        if ($objectType instanceof UnionType && $this->isChild($objectType, $name)) {
            return null;
        }

        if ($objectType instanceof UnionType && $this->hasNullType($objectType)) {
            return null;
        }

        $isSameObject = $this->isObjectType($previousVar, $name);
        if (! $isSameObject) {
            return null;
        }

        if ($if->cond === $instanceof) {
            $this->unwrapStmts($if->stmts, $if);
            $this->removeNode($if);

            return null;
        }

        $this->removeNode($if);
        return $if;
    }

    private function isChild(UnionType $type, string $name): bool
    {
        /** @var Type $firstType */
        $firstType = current($type->getTypes());

        if (! $firstType instanceof ObjectType) {
            return false;
        }

        $className = $firstType->getClassName();
        return is_a($className, $name, true);
    }

    private function hasNullType(UnionType $type): bool
    {
        $types = $type->getTypes();

        foreach ($types as $type) {
            if ($type instanceof NullType) {
                return true;
            }
        }

        return false;
    }
}
