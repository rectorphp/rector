<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\DowngradePhp72\NodeAnalyzer\RegexFuncAnalyzer;
use Rector\DowngradePhp72\NodeManipulator\BitwiseFlagCleaner;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\DowngradePregUnmatchedAsNullConstantRectorTest
 */
final class DowngradePregUnmatchedAsNullConstantRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BitwiseFlagCleaner $bitwiseFlagCleaner;
    /**
     * @readonly
     */
    private RegexFuncAnalyzer $regexFuncAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @see https://www.php.net/manual/en/function.preg-match.php
     * @var string
     */
    private const UNMATCHED_NULL_FLAG = 'PREG_UNMATCHED_AS_NULL';
    public function __construct(BitwiseFlagCleaner $bitwiseFlagCleaner, RegexFuncAnalyzer $regexFuncAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->bitwiseFlagCleaner = $bitwiseFlagCleaner;
        $this->regexFuncAnalyzer = $regexFuncAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class, ClassConst::class, If_::class];
    }
    /**
     * @param Expression|ClassConst|If_ $node
     * @return Stmt[]|ClassConst|null
     */
    public function refactor(Node $node)
    {
        if ($node instanceof ClassConst) {
            return $this->refactorClassConst($node);
        }
        $funcCall = $this->matchRegexFuncCall($node);
        if (!$funcCall instanceof FuncCall) {
            return null;
        }
        $args = $funcCall->getArgs();
        $variable = $args[2]->value;
        if (!$variable instanceof Variable) {
            return null;
        }
        if (!isset($args[3])) {
            return null;
        }
        $flagsExpr = $args[3]->value;
        if ($flagsExpr instanceof BitwiseOr) {
            $this->bitwiseFlagCleaner->cleanFuncCall($funcCall, $flagsExpr, self::UNMATCHED_NULL_FLAG, null);
            if ($this->nodeComparator->areNodesEqual($flagsExpr, $args[3]->value)) {
                return null;
            }
            return $this->handleEmptyStringToNullMatch($funcCall, $variable, $node);
        }
        if (!$flagsExpr instanceof ConstFetch) {
            return null;
        }
        if (!$this->isName($flagsExpr, self::UNMATCHED_NULL_FLAG)) {
            return null;
        }
        unset($funcCall->args[3]);
        return $this->handleEmptyStringToNullMatch($funcCall, $variable, $node);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove PREG_UNMATCHED_AS_NULL from preg_match and set null value on empty string matched on each match', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_UNMATCHED_AS_NULL);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches);

        array_walk_recursive($matches, function (&$value) {
            if ($value === '') {
                $value = null;
            }
        });
    }
}
CODE_SAMPLE
)]);
    }
    private function refactorClassConst(ClassConst $classConst) : ?ClassConst
    {
        foreach ($classConst->consts as $key => $singleClassConst) {
            if (!$singleClassConst->value instanceof ConstFetch) {
                continue;
            }
            if (!$this->isName($singleClassConst->value, self::UNMATCHED_NULL_FLAG)) {
                continue;
            }
            $classConst->consts[$key]->value = new Int_(512);
            return $classConst;
        }
        return null;
    }
    /**
     * @return Stmt|Stmt[]|null
     */
    private function handleEmptyStringToNullMatch(FuncCall $funcCall, Variable $variable, Node $node)
    {
        $variablePass = new Variable('value');
        $assign = new Assign($variablePass, $this->nodeFactory->createNull());
        $if = $this->createIf($variablePass, $assign);
        $param = new Param($variablePass, null, null, \true);
        $closure = new Closure(['params' => [$param], 'stmts' => [$if]]);
        $arguments = $this->nodeFactory->createArgs([$variable, $closure]);
        $arrayWalkRecursiveFuncCall = $this->nodeFactory->createFuncCall('array_walk_recursive', $arguments);
        return $this->processReplace($funcCall, $arrayWalkRecursiveFuncCall, $node);
    }
    /**
     * @return Stmt|Stmt[]|null
     */
    private function processReplace(FuncCall $funcCall, FuncCall $replaceEmptyStringToNull, Stmt $stmt)
    {
        if ($stmt instanceof If_ && $stmt->cond === $funcCall) {
            return $this->processInIf($stmt, $replaceEmptyStringToNull);
        }
        return [$stmt, new Expression($replaceEmptyStringToNull)];
    }
    private function processInIf(If_ $if, FuncCall $funcCall) : ?Stmt
    {
        $cond = $if->cond;
        if ($cond instanceof Identical) {
            return null;
        }
        if ($cond instanceof BooleanNot) {
            return null;
        }
        return $this->handleNotInIdenticalAndBooleanNot($if, $funcCall);
    }
    private function handleNotInIdenticalAndBooleanNot(If_ $if, FuncCall $funcCall) : Stmt
    {
        if ($if->stmts !== []) {
            return $if->stmts[0];
        }
        $if->stmts[0] = new Expression($funcCall);
        return $if;
    }
    /**
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\If_ $stmt
     */
    private function matchRegexFuncCall($stmt) : ?FuncCall
    {
        $funcCalls = $this->betterNodeFinder->findInstancesOf($stmt, [FuncCall::class]);
        foreach ($funcCalls as $funcCall) {
            if (!$this->regexFuncAnalyzer->matchRegexFuncCall($funcCall) instanceof FuncCall) {
                continue;
            }
            return $funcCall;
        }
        return null;
    }
    private function createIf(Variable $variable, Assign $assign) : If_
    {
        $conditionIdentical = new Identical($variable, new String_(''));
        return new If_($conditionIdentical, ['stmts' => [new Expression($assign)]]);
    }
}
