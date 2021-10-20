<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\FuncCall;

use RectorPrefix20211020\Nette\NotImplementedException;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
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
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\DowngradePregUnmatchedAsNullConstantRectorTest
 */
final class DowngradePregUnmatchedAsNullConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const REGEX_FUNCTION_NAMES = ['preg_match', 'preg_match_all'];
    /**
     * @var string
     */
    private const FLAG = 'PREG_UNMATCHED_AS_NULL';
    /**
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
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
            return $this->processsClassConst($node);
        }
        if (!$this->isRegexFunctionNames($node)) {
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
            $this->cleanBitWiseOrFlags($node, $flags);
            if (!$this->nodeComparator->areNodesEqual($flags, $args[3]->value)) {
                return $this->handleEmptyStringToNullMatch($node, $variable);
            }
            return null;
        }
        if (!$flags instanceof \PhpParser\Node\Expr\ConstFetch) {
            return null;
        }
        if (!$this->isName($flags, self::FLAG)) {
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
    private function processsClassConst(\PhpParser\Node\Stmt\ClassConst $classConst) : \PhpParser\Node\Stmt\ClassConst
    {
        foreach ($classConst->consts as $key => $singleClassConst) {
            if (!$singleClassConst->value instanceof \PhpParser\Node\Expr\ConstFetch) {
                continue;
            }
            if (!$this->isName($singleClassConst->value, self::FLAG)) {
                continue;
            }
            $classConst->consts[$key]->value = new \PhpParser\Node\Scalar\LNumber(512);
            return $classConst;
        }
        return $classConst;
    }
    private function isRegexFunctionNames(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        if ($this->isNames($funcCall, self::REGEX_FUNCTION_NAMES)) {
            return \true;
        }
        $variable = $funcCall->name;
        if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        /** @var Assign|null $assignExprVariable */
        $assignExprVariable = $this->betterNodeFinder->findFirstPreviousOfNode($funcCall, function (\PhpParser\Node $node) use($variable) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->var, $variable);
        });
        if (!$assignExprVariable instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        $expr = $assignExprVariable->expr;
        if (!$expr instanceof \PhpParser\Node\Expr\Ternary) {
            return \false;
        }
        if (!$expr->if instanceof \PhpParser\Node\Scalar\String_) {
            return \false;
        }
        if (!$expr->else instanceof \PhpParser\Node\Scalar\String_) {
            return \false;
        }
        return \in_array($expr->if->value, self::REGEX_FUNCTION_NAMES, \true) && \in_array($expr->else->value, self::REGEX_FUNCTION_NAMES, \true);
    }
    private function cleanBitWiseOrFlags(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\BinaryOp\BitwiseOr $bitwiseOr, ?\PhpParser\Node\Expr $expr = null) : void
    {
        if ($bitwiseOr->left instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr) {
            /** @var BitwiseOr $leftLeft */
            $leftLeft = $bitwiseOr->left;
            if ($leftLeft->left instanceof \PhpParser\Node\Expr\ConstFetch && $this->isName($leftLeft->left, self::FLAG)) {
                $bitwiseOr = new \PhpParser\Node\Expr\BinaryOp\BitwiseOr($leftLeft->right, $bitwiseOr->right);
            }
            /** @var BitwiseOr $leftRight */
            $leftRight = $bitwiseOr->left;
            if ($leftRight->right instanceof \PhpParser\Node\Expr\ConstFetch && $this->isName($leftRight->right, self::FLAG)) {
                $bitwiseOr = new \PhpParser\Node\Expr\BinaryOp\BitwiseOr($leftRight->left, $bitwiseOr->right);
            }
            if ($bitwiseOr->left instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr) {
                $this->cleanBitWiseOrFlags($funcCall, $bitwiseOr->left, $bitwiseOr->right);
                return;
            }
        }
        if ($expr instanceof \PhpParser\Node\Expr) {
            $bitwiseOr = new \PhpParser\Node\Expr\BinaryOp\BitwiseOr($bitwiseOr, $expr);
        }
        $this->assignThirdArgsValue($funcCall, $bitwiseOr);
    }
    private function assignThirdArgsValue(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\BinaryOp\BitwiseOr $bitwiseOr) : void
    {
        if ($bitwiseOr instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr && $bitwiseOr->right instanceof \PhpParser\Node\Expr\ConstFetch && $this->isName($bitwiseOr->right, self::FLAG)) {
            $bitwiseOr = $bitwiseOr->left;
        }
        if ($bitwiseOr instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr && $bitwiseOr->left instanceof \PhpParser\Node\Expr\ConstFetch && $this->isName($bitwiseOr->left, self::FLAG)) {
            $bitwiseOr = $bitwiseOr->right;
        }
        if (!$funcCall->args[3] instanceof \PhpParser\Node\Arg) {
            return;
        }
        $funcCall->args[3]->value = $bitwiseOr;
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
        $replaceEmptystringToNull = $this->nodeFactory->createFuncCall('array_walk_recursive', $arguments);
        return $this->processReplace($funcCall, $replaceEmptystringToNull);
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
            throw new \RectorPrefix20211020\Nette\NotImplementedException();
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
    private function processInIf(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\FuncCall $replaceEmptystringToNull) : \PhpParser\Node\Expr\FuncCall
    {
        $cond = $if->cond;
        if (!$cond instanceof \PhpParser\Node\Expr\BinaryOp\Identical && !$cond instanceof \PhpParser\Node\Expr\BooleanNot) {
            $this->handleNotInIdenticalAndBooleanNot($if, $replaceEmptystringToNull);
        }
        if ($cond instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            $valueCompare = $cond->left === $funcCall ? $cond->right : $cond->left;
            if ($this->valueResolver->isFalse($valueCompare)) {
                $this->nodesToAddCollector->addNodeAfterNode($replaceEmptystringToNull, $if);
            }
        }
        if ($cond instanceof \PhpParser\Node\Expr\BooleanNot) {
            $this->nodesToAddCollector->addNodeAfterNode($replaceEmptystringToNull, $if);
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
