<?php

declare (strict_types=1);
namespace Rector\Php74\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\ClosureUse;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeTypeResolver\NodeTypeResolver;
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
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, ArrayChecker $arrayChecker, PhpDocInfoFactory $phpDocInfoFactory, NodeTypeResolver $nodeTypeResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->arrayChecker = $arrayChecker;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function matchArrowFunctionExpr(Closure $closure) : ?Expr
    {
        if (\count($closure->stmts) !== 1) {
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
        if ($this->shouldSkipMoreSpecificTypeWithVarDoc($return, $return->expr)) {
            return null;
        }
        return $return->expr;
    }
    /**
     * Ensure @var doc usage with more specific type on purpose to be skipped
     */
    private function shouldSkipMoreSpecificTypeWithVarDoc(Return_ $return, Expr $expr) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($return);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return \false;
        }
        $varType = $phpDocInfo->getVarType();
        if ($varType instanceof MixedType) {
            return \false;
        }
        $variableName = \ltrim($varTagValueNode->variableName, '$');
        $variable = $this->betterNodeFinder->findFirst($expr, static fn(Node $node): bool => $node instanceof Variable && $node->name === $variableName);
        if (!$variable instanceof Variable) {
            return \false;
        }
        $nativeVariableType = $this->nodeTypeResolver->getNativeType($variable);
        // not equal with native type means more specific type
        return !$nativeVariableType->equals($varType);
    }
    private function shouldSkipForUsedReferencedValue(Closure $closure) : bool
    {
        $referencedValues = $this->resolveReferencedUseVariablesFromClosure($closure);
        if ($referencedValues === []) {
            return \false;
        }
        $isFoundInStmt = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($closure, function (Node $node) use($referencedValues) : bool {
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
    private function isFoundInInnerUses(Closure $node, array $referencedValues) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, function (Node $subNode) use($referencedValues) : bool {
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
    private function resolveReferencedUseVariablesFromClosure(Closure $closure) : array
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
