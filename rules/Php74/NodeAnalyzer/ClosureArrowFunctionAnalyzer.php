<?php

declare (strict_types=1);
namespace Rector\Php74\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\ClosureUse;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeAnalyzer\CompactFuncCallAnalyzer;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Util\ArrayChecker;
final class ClosureArrowFunctionAnalyzer
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeComparator $nodeComparator;
    /**
     * @readonly
     */
    private ArrayChecker $arrayChecker;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private CompactFuncCallAnalyzer $compactFuncCallAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, ArrayChecker $arrayChecker, PhpDocInfoFactory $phpDocInfoFactory, CompactFuncCallAnalyzer $compactFuncCallAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->arrayChecker = $arrayChecker;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
    }
    public function matchArrowFunctionExpr(Closure $closure): ?Expr
    {
        if (count($closure->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $closure->stmts[0];
        if (!$onlyStmt instanceof Return_) {
            return null;
        }
        /** @var Return_ $return */
        $return = $onlyStmt;
        if (!$return->expr instanceof Expr) {
            return null;
        }
        if ($this->shouldSkipForUsedReferencedValue($closure)) {
            return null;
        }
        if ($this->shouldSkipForUseVariableUsedByCompact($closure)) {
            return null;
        }
        if ($this->shouldSkipMoreSpecificTypeWithVarDoc($return)) {
            return null;
        }
        return $return->expr;
    }
    private function shouldSkipForUseVariableUsedByCompact(Closure $closure): bool
    {
        $variables = array_map(fn(ClosureUse $use): Variable => $use->var, $closure->uses);
        if ($variables === []) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($closure, function (Node $node) use ($variables): bool {
            if (!$node instanceof FuncCall) {
                return \false;
            }
            foreach ($variables as $variable) {
                if ($this->compactFuncCallAnalyzer->isInCompact($node, $variable)) {
                    return \true;
                }
            }
            return \false;
        });
    }
    /**
     * Ensure @var doc usage to be skipped, as arrow functions do not support
     * inline @var annotations for type narrowing (e.g. generic types like Builder<Team>)
     */
    private function shouldSkipMoreSpecificTypeWithVarDoc(Return_ $return): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($return);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        return $varTagValueNode instanceof VarTagValueNode;
    }
    private function shouldSkipForUsedReferencedValue(Closure $closure): bool
    {
        $referencedValues = $this->resolveReferencedUseVariablesFromClosure($closure);
        if ($referencedValues === []) {
            return \false;
        }
        $isFoundInStmt = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($closure, function (Node $node) use ($referencedValues): bool {
            foreach ($referencedValues as $referencedValue) {
                if ($this->nodeComparator->areNodesEqual($node, $referencedValue)) {
                    return \true;
                }
            }
            return \false;
        });
        if ($isFoundInStmt) {
            return \true;
        }
        return $this->isFoundInInnerUses($closure, $referencedValues);
    }
    /**
     * @param Variable[] $referencedValues
     */
    private function isFoundInInnerUses(Closure $node, array $referencedValues): bool
    {
        return (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, function (Node $subNode) use ($referencedValues): bool {
            if (!$subNode instanceof Closure) {
                return \false;
            }
            foreach ($referencedValues as $referencedValue) {
                $isFoundInInnerUses = $this->arrayChecker->doesExist($subNode->uses, fn(ClosureUse $closureUse): bool => $closureUse->byRef && $this->nodeComparator->areNodesEqual($closureUse->var, $referencedValue));
                if ($isFoundInInnerUses) {
                    return \true;
                }
            }
            return \false;
        });
    }
    /**
     * @return Variable[]
     */
    private function resolveReferencedUseVariablesFromClosure(Closure $closure): array
    {
        $referencedValues = [];
        /** @var ClosureUse $use */
        foreach ($closure->uses as $use) {
            if ($use->byRef) {
                $referencedValues[] = $use->var;
            }
        }
        return $referencedValues;
    }
}
