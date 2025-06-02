<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\TryCatch;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\TryCatch\RemoveDeadCatchRector\RemoveDeadCatchRectorTest
 */
final class RemoveDeadCatchRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove dead catches', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // some code
        } catch (RuntimeException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        } catch (Throwable $throwable) {
            throw $throwable;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        try {
            // some code
        } catch (RuntimeException $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [TryCatch::class];
    }
    /**
     * @param TryCatch $node
     * @return TryCatch|null
     */
    public function refactor(Node $node) : ?Node
    {
        $catches = $node->catches;
        if (\count($catches) === 1) {
            return null;
        }
        $hasChanged = \false;
        $maxIndexCatches = \count($catches) - 1;
        foreach ($catches as $key => $catchItem) {
            if (!$this->isJustThrowedSameVariable($catchItem)) {
                continue;
            }
            /** @var FullyQualified $type */
            $type = $catchItem->types[0];
            if ($this->shouldSkipNextCatchClassParentWithSpecialTreatment($catches, $type, $key, $maxIndexCatches)) {
                continue;
            }
            unset($catches[$key]);
            $hasChanged = \true;
        }
        if (!$hasChanged || $catches === []) {
            return null;
        }
        $node->catches = $catches;
        return $node;
    }
    private function isJustThrowedSameVariable(Catch_ $catch) : bool
    {
        if ($this->isEmpty($catch->stmts)) {
            return \false;
        }
        $catchItemStmt = $catch->stmts[0];
        if (!($catchItemStmt instanceof Expression && $catchItemStmt->expr instanceof Throw_)) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($catch->var, $catchItemStmt->expr->expr)) {
            return \false;
        }
        // too complex to check
        if (\count($catch->types) !== 1) {
            return \false;
        }
        $type = $catch->types[0];
        return $type instanceof FullyQualified;
    }
    /**
     * @param Catch_[] $catches
     */
    private function shouldSkipNextCatchClassParentWithSpecialTreatment(array $catches, FullyQualified $fullyQualified, int $key, int $maxIndexCatches) : bool
    {
        for ($index = $key + 1; $index <= $maxIndexCatches; ++$index) {
            if (!isset($catches[$index])) {
                continue;
            }
            $nextCatch = $catches[$index];
            // too complex to check
            if (\count($nextCatch->types) !== 1) {
                return \true;
            }
            $nextCatchType = $nextCatch->types[0];
            if (!$nextCatchType instanceof FullyQualified) {
                return \true;
            }
            if (!$this->isObjectType($fullyQualified, new ObjectType($nextCatchType->toString()))) {
                continue;
            }
            if (!$this->isJustThrowedSameVariable($nextCatch)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isEmpty(array $stmts) : bool
    {
        if ($stmts === []) {
            return \true;
        }
        if (\count($stmts) > 1) {
            return \false;
        }
        return $stmts[0] instanceof Nop;
    }
}
