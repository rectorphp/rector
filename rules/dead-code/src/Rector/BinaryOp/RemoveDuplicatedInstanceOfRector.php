<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\BinaryOp;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\BinaryOp\RemoveDuplicatedInstanceOfRector\RemoveDuplicatedInstanceOfRectorTest
 */
final class RemoveDuplicatedInstanceOfRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $duplicatedInstanceOfs = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove duplicated instanceof in one call', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        $isIt = $value instanceof A || $value instanceof A;
        $isIt = $value instanceof A && $value instanceof A;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value): void
    {
        $isIt = $value instanceof A;
        $isIt = $value instanceof A;
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
        return [BinaryOp::class];
    }

    /**
     * @param BinaryOp $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->resolveDuplicatedInstancesOf($node);
        if ($this->duplicatedInstanceOfs === []) {
            return null;
        }

        return $this->traverseBinaryOpAndRemoveDuplicatedInstanceOfs($node);
    }

    private function resolveDuplicatedInstancesOf(BinaryOp $binaryOp): void
    {
        $this->duplicatedInstanceOfs = [];

        /** @var Instanceof_[] $instanceOfs */
        $instanceOfs = $this->betterNodeFinder->findInstanceOf([$binaryOp], Instanceof_::class);

        $instanceOfsByClass = [];
        foreach ($instanceOfs as $instanceOf) {
            $variableClassKey = $this->createUniqueKeyForInstanceOf($instanceOf);
            if ($variableClassKey === null) {
                continue;
            }

            $instanceOfsByClass[$variableClassKey][] = $instanceOf;
        }

        foreach ($instanceOfsByClass as $variableClassKey => $instanceOfs) {
            if (count($instanceOfs) < 2) {
                unset($instanceOfsByClass[$variableClassKey]);
            }
        }

        $this->duplicatedInstanceOfs = array_keys($instanceOfsByClass);
    }

    private function traverseBinaryOpAndRemoveDuplicatedInstanceOfs(BinaryOp $binaryOp): Node
    {
        $this->traverseNodesWithCallable([&$binaryOp], function (Node $node): ?Node {
            if (! $node instanceof BinaryOp) {
                return null;
            }

            if ($node->left instanceof Instanceof_) {
                return $this->processBinaryWithFirstInstaneOf($node->left, $node->right);
            }

            if ($node->right instanceof Instanceof_) {
                return $this->processBinaryWithFirstInstaneOf($node->right, $node->left);
            }

            return null;
        });

        return $binaryOp;
    }

    private function createUniqueKeyForInstanceOf(Instanceof_ $instanceof): ?string
    {
        if (! $instanceof->expr instanceof Variable) {
            return null;
        }
        $variableName = $this->getName($instanceof->expr);
        if ($variableName === null) {
            return null;
        }

        $className = $this->getName($instanceof->class);
        if ($className === null) {
            return null;
        }

        return $variableName . '_' . $className;
    }

    private function processBinaryWithFirstInstaneOf(Instanceof_ $instanceof, Expr $otherExpr): ?Expr
    {
        $variableClassKey = $this->createUniqueKeyForInstanceOf($instanceof);

        if (! in_array($variableClassKey, $this->duplicatedInstanceOfs, true)) {
            return null;
        }

        // remove just once
        $this->removeClassFromDuplicatedInstanceOfs($variableClassKey);

        // remove left instanceof
        return $otherExpr;
    }

    private function removeClassFromDuplicatedInstanceOfs(string $variableClassKey): void
    {
        // remove just once
        unset($this->duplicatedInstanceOfs[array_search($variableClassKey, $this->duplicatedInstanceOfs, true)]);
    }
}
