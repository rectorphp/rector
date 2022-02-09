<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\FuncCall;

use RectorPrefix20220209\Nette\NotImplementedException;
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
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\NodeAnalyzer\RegexFuncAnalyzer;
use Rector\DowngradePhp72\NodeManipulator\BitwiseFlagCleaner;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\DowngradePregUnmatchedAsNullConstantRectorTest
 */
final class DowngradePregUnmatchedAsNullConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @see https://www.php.net/manual/en/function.preg-match.php
     * @var string
     */
    private const UNMATCHED_NULL_FLAG = 'PREG_UNMATCHED_AS_NULL';
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeManipulator\BitwiseFlagCleaner
     */
    private $bitwiseFlagCleaner;
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeAnalyzer\RegexFuncAnalyzer
     */
    private $regexFuncAnalyzer;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\DowngradePhp72\NodeManipulator\BitwiseFlagCleaner $bitwiseFlagCleaner, \Rector\DowngradePhp72\NodeAnalyzer\RegexFuncAnalyzer $regexFuncAnalyzer)
    {
        $this->ifManipulator = $ifManipulator;
        $this->bitwiseFlagCleaner = $bitwiseFlagCleaner;
        $this->regexFuncAnalyzer = $regexFuncAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Stmt\ClassConst::class];
    }
    /**
     * @param FuncCall|ClassConst $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassConst) {
            return $this->refactorClassConst($node);
        }
        if (!$this->regexFuncAnalyzer->isRegexFunctionNames($node)) {
            return null;
        }
        $args = $node->args;
        if (!isset($args[3])) {
            return null;
        }
        $flags = $args[3]->value;
        /** @var Variable $variable */
        $variable = $args[2]->value;
        if ($flags instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr) {
            $this->bitwiseFlagCleaner->cleanFuncCall($node, $flags, null, self::UNMATCHED_NULL_FLAG);
            if (!$this->nodeComparator->areNodesEqual($flags, $args[3]->value)) {
                return $this->handleEmptyStringToNullMatch($node, $variable);
            }
            return null;
        }
        if (!$flags instanceof \PhpParser\Node\Expr\ConstFetch) {
            return null;
        }
        if (!$this->isName($flags, self::UNMATCHED_NULL_FLAG)) {
            return null;
        }
        $node = $this->handleEmptyStringToNullMatch($node, $variable);
        unset($node->args[3]);
        return $node;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove PREG_UNMATCHED_AS_NULL from preg_match and set null value on empty string matched on each match', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    private function refactorClassConst(\PhpParser\Node\Stmt\ClassConst $classConst) : \PhpParser\Node\Stmt\ClassConst
    {
        foreach ($classConst->consts as $key => $singleClassConst) {
            if (!$singleClassConst->value instanceof \PhpParser\Node\Expr\ConstFetch) {
                continue;
            }
            if (!$this->isName($singleClassConst->value, self::UNMATCHED_NULL_FLAG)) {
                continue;
            }
            $classConst->consts[$key]->value = new \PhpParser\Node\Scalar\LNumber(512);
            return $classConst;
        }
        return $classConst;
    }
    private function handleEmptyStringToNullMatch(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\Variable $variable) : \PhpParser\Node\Expr\FuncCall
    {
        $closure = new \PhpParser\Node\Expr\Closure();
        $variablePass = new \PhpParser\Node\Expr\Variable('value');
        $param = new \PhpParser\Node\Param($variablePass);
        $param->byRef = \true;
        $closure->params = [$param];
        $assign = new \PhpParser\Node\Expr\Assign($variablePass, $this->nodeFactory->createNull());
        $if = $this->ifManipulator->createIfExpr(new \PhpParser\Node\Expr\BinaryOp\Identical($variablePass, new \PhpParser\Node\Scalar\String_('')), new \PhpParser\Node\Stmt\Expression($assign));
        $closure->stmts[0] = $if;
        $arguments = $this->nodeFactory->createArgs([$variable, $closure]);
        $replaceEmptyStringToNull = $this->nodeFactory->createFuncCall('array_walk_recursive', $arguments);
        return $this->processReplace($funcCall, $replaceEmptyStringToNull);
    }
    private function processReplace(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\FuncCall $replaceEmptystringToNull) : \PhpParser\Node\Expr\FuncCall
    {
        $parent = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Stmt\Expression) {
            $this->nodesToAddCollector->addNodeAfterNode($replaceEmptystringToNull, $funcCall);
            return $funcCall;
        }
        if ($parent instanceof \PhpParser\Node\Stmt\If_ && $parent->cond === $funcCall) {
            return $this->processInIf($parent, $funcCall, $replaceEmptystringToNull);
        }
        if (!$parent instanceof \PhpParser\Node) {
            throw new \RectorPrefix20220209\Nette\NotImplementedException();
        }
        $if = $parent->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Expr\BooleanNot) {
            return $this->processInIf($if, $funcCall, $replaceEmptystringToNull);
        }
        if (!$parent instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        if (!$if instanceof \PhpParser\Node\Stmt\If_) {
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        return $this->processInIf($if, $funcCall, $replaceEmptystringToNull);
    }
    private function processInIf(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\FuncCall $replaceEmptyStringToNull) : \PhpParser\Node\Expr\FuncCall
    {
        $cond = $if->cond;
        if (!$cond instanceof \PhpParser\Node\Expr\BinaryOp\Identical && !$cond instanceof \PhpParser\Node\Expr\BooleanNot) {
            $this->handleNotInIdenticalAndBooleanNot($if, $replaceEmptyStringToNull);
        }
        if ($cond instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            $valueCompare = $cond->left === $funcCall ? $cond->right : $cond->left;
            if ($this->valueResolver->isFalse($valueCompare)) {
                $this->nodesToAddCollector->addNodeAfterNode($replaceEmptyStringToNull, $if);
            }
        }
        if ($cond instanceof \PhpParser\Node\Expr\BooleanNot) {
            $this->nodesToAddCollector->addNodeAfterNode($replaceEmptyStringToNull, $if);
        }
        return $funcCall;
    }
    private function handleNotInIdenticalAndBooleanNot(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr\FuncCall $funcCall) : void
    {
        if ($if->stmts !== []) {
            $firstStmt = $if->stmts[0];
            $this->nodesToAddCollector->addNodeBeforeNode($funcCall, $firstStmt);
        } else {
            $if->stmts[0] = new \PhpParser\Node\Stmt\Expression($funcCall);
        }
    }
}
