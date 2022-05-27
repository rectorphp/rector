<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
final class SwitchAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param Case_[] $cases
     */
    public function hasDifferentTypeCases(array $cases) : bool
    {
        $types = [];
        foreach ($cases as $case) {
            if ($case->cond instanceof \PhpParser\Node\Expr) {
                $types[] = $this->nodeTypeResolver->getType($case->cond);
            }
        }
        if ($types === []) {
            return \false;
        }
        $uniqueTypes = $this->typeFactory->uniquateTypes($types);
        return \count($uniqueTypes) > 1;
    }
    public function hasEachCaseBreak(\PhpParser\Node\Stmt\Switch_ $switch) : bool
    {
        $totalCases = \count($switch->cases);
        if ($totalCases === 1) {
            return $this->containsCaseReturn($switch->cases[0]);
        }
        foreach ($switch->cases as $key => $case) {
            if ($key === $totalCases - 1) {
                return \true;
            }
            if ($this->hasBreakOrReturnOrEmpty($case)) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    public function hasEachCaseSingleStmt(\PhpParser\Node\Stmt\Switch_ $switch) : bool
    {
        foreach ($switch->cases as $case) {
            if ($case->cond === null) {
                continue;
            }
            $stmtsWithoutBreak = \array_filter($case->stmts, function (\PhpParser\Node $node) : bool {
                return !$node instanceof \PhpParser\Node\Stmt\Break_;
            });
            if (\count($stmtsWithoutBreak) !== 1) {
                return \false;
            }
        }
        return \true;
    }
    public function hasDefaultSingleStmt(\PhpParser\Node\Stmt\Switch_ $switch) : bool
    {
        foreach ($switch->cases as $case) {
            if ($case->cond === null) {
                $stmtsWithoutBreak = \array_filter($case->stmts, function (\PhpParser\Node $node) : bool {
                    return !$node instanceof \PhpParser\Node\Stmt\Break_;
                });
                return \count($stmtsWithoutBreak) === 1;
            }
        }
        return \false;
    }
    private function hasBreakOrReturnOrEmpty(\PhpParser\Node\Stmt\Case_ $case) : bool
    {
        if ($case->stmts === []) {
            return \true;
        }
        foreach ($case->stmts as $caseStmt) {
            if ($caseStmt instanceof \PhpParser\Node\Stmt\Break_) {
                return \true;
            }
            if ($caseStmt instanceof \PhpParser\Node\Stmt\Return_) {
                return \true;
            }
        }
        return \false;
    }
    private function containsCaseReturn(\PhpParser\Node\Stmt\Case_ $case) : bool
    {
        foreach ($case->stmts as $stmt) {
            if ($stmt instanceof \PhpParser\Node\Stmt\Return_) {
                return \true;
            }
        }
        return \false;
    }
}
