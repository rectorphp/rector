<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\CodingStyle\ValueObject\VariableAndExprAssign;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassMethod\BinaryOpStandaloneAssignsToDirectRector\BinaryOpStandaloneAssignsToDirectRectorTest
 */
final class BinaryOpStandaloneAssignsToDirectRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change 2 standalone assigns to variable then binary op to direct binary op', [new CodeSample(<<<'CODE_SAMPLE'
function run()
{
    $value = 100;
    $anotherValue = 200;

    return 100 <=> 200;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run()
{
    return 100 <=> 200;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        if (count($node->stmts) !== 3) {
            return null;
        }
        $firstStmt = $node->stmts[0];
        $secondStmt = $node->stmts[1];
        $thirdStmt = $node->stmts[2];
        if (!$thirdStmt instanceof Return_) {
            return null;
        }
        $firstVariableAndExprAssign = $this->matchToVariableAssignExpr($firstStmt);
        if (!$firstVariableAndExprAssign instanceof VariableAndExprAssign) {
            return null;
        }
        $secondVariableAndExprAssign = $this->matchToVariableAssignExpr($secondStmt);
        if (!$secondVariableAndExprAssign instanceof VariableAndExprAssign) {
            return null;
        }
        if (!$thirdStmt->expr instanceof BinaryOp) {
            return null;
        }
        $binaryOp = $thirdStmt->expr;
        if (!$this->nodeComparator->areNodesEqual($binaryOp->left, $firstVariableAndExprAssign->getVariable())) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($binaryOp->right, $secondVariableAndExprAssign->getVariable())) {
            return null;
        }
        $resolveParamByRefVariables = $this->resolveParamByRefVariables($node);
        if ($this->isNames($binaryOp->left, $resolveParamByRefVariables)) {
            return null;
        }
        if ($this->isNames($binaryOp->right, $resolveParamByRefVariables)) {
            return null;
        }
        $binaryOp->left = $firstVariableAndExprAssign->getExpr();
        $binaryOp->right = $secondVariableAndExprAssign->getExpr();
        $node->stmts = [$thirdStmt];
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::VARIADIC_PARAM;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function resolveParamByRefVariables($node): array
    {
        $paramByRefVariables = [];
        foreach ($node->params as $param) {
            if (!$param->var instanceof Variable) {
                continue;
            }
            if (!$param->byRef) {
                continue;
            }
            $paramByRefVariables[] = $this->getName($param);
        }
        return $paramByRefVariables;
    }
    private function matchToVariableAssignExpr(Stmt $stmt): ?VariableAndExprAssign
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$assign->var instanceof Variable) {
            return null;
        }
        // skip complex cases
        if ($assign->expr instanceof CallLike && !$assign->expr->isFirstClassCallable() && $assign->expr->getArgs() !== []) {
            return null;
        }
        if ($assign->expr instanceof BinaryOp) {
            return null;
        }
        return new VariableAndExprAssign($assign->var, $assign->expr);
    }
}
