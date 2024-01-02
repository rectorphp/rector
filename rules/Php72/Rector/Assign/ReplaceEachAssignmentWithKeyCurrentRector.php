<?php

declare (strict_types=1);
namespace Rector\Php72\Rector\Assign;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector\ReplaceEachAssignmentWithKeyCurrentRectorTest
 */
final class ReplaceEachAssignmentWithKeyCurrentRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const KEY = 'key';
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_EACH_OUTSIDE_LOOP;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace each() assign outside loop', [new CodeSample(<<<'CODE_SAMPLE'
$array = ['b' => 1, 'a' => 2];

$eachedArray = each($array);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$array = ['b' => 1, 'a' => 2];

$eachedArray[1] = current($array);
$eachedArray['value'] = current($array);
$eachedArray[0] = key($array);
$eachedArray['key'] = key($array);

next($array);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Stmt[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $assign = $node->expr;
        if ($this->shouldSkip($assign)) {
            return null;
        }
        /** @var FuncCall $eachFuncCall */
        $eachFuncCall = $assign->expr;
        if ($eachFuncCall->isFirstClassCallable()) {
            return null;
        }
        if (!isset($eachFuncCall->getArgs()[0])) {
            return null;
        }
        $assignVariable = $assign->var;
        $eachedVariable = $eachFuncCall->getArgs()[0]->value;
        return $this->createNewStmts($assignVariable, $eachedVariable);
    }
    private function shouldSkip(Assign $assign) : bool
    {
        if (!$assign->expr instanceof FuncCall) {
            return \true;
        }
        if (!$this->nodeNameResolver->isName($assign->expr, 'each')) {
            return \true;
        }
        return $assign->var instanceof List_;
    }
    /**
     * @return Stmt[]
     */
    private function createNewStmts(Expr $assignVariable, Expr $eachedVariable) : array
    {
        $exprs = [$this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 1, 'current'), $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 'value', 'current'), $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 0, self::KEY), $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, self::KEY, self::KEY), $this->nodeFactory->createFuncCall('next', [new Arg($eachedVariable)])];
        return \array_map(static function (Expr $expr) : Expression {
            return new Expression($expr);
        }, $exprs);
    }
    /**
     * @param string|int $dimValue
     */
    private function createDimFetchAssignWithFuncCall(Expr $assignVariable, Expr $eachedVariable, $dimValue, string $functionName) : Assign
    {
        $dimExpr = BuilderHelpers::normalizeValue($dimValue);
        $arrayDimFetch = new ArrayDimFetch($assignVariable, $dimExpr);
        return new Assign($arrayDimFetch, $this->nodeFactory->createFuncCall($functionName, [new Arg($eachedVariable)]));
    }
}
