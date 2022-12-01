<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\FuncCall;

use RectorPrefix202212\Nette\NotImplementedException;
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
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\DowngradePregUnmatchedAsNullConstantRectorTest
 */
final class DowngradePregUnmatchedAsNullConstantRector extends AbstractRector
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
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(IfManipulator $ifManipulator, BitwiseFlagCleaner $bitwiseFlagCleaner, RegexFuncAnalyzer $regexFuncAnalyzer, NodesToAddCollector $nodesToAddCollector)
    {
        $this->ifManipulator = $ifManipulator;
        $this->bitwiseFlagCleaner = $bitwiseFlagCleaner;
        $this->regexFuncAnalyzer = $regexFuncAnalyzer;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class, ClassConst::class];
    }
    /**
     * @param FuncCall|ClassConst $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassConst) {
            return $this->refactorClassConst($node);
        }
        if (!$this->regexFuncAnalyzer->isRegexFunctionNames($node)) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[3])) {
            return null;
        }
        $flags = $args[3]->value;
        /** @var Variable $variable */
        $variable = $args[2]->value;
        if ($flags instanceof BitwiseOr) {
            $this->bitwiseFlagCleaner->cleanFuncCall($node, $flags, self::UNMATCHED_NULL_FLAG, null);
            if (!$this->nodeComparator->areNodesEqual($flags, $args[3]->value)) {
                return $this->handleEmptyStringToNullMatch($node, $variable);
            }
            return null;
        }
        if (!$flags instanceof ConstFetch) {
            return null;
        }
        if (!$this->isName($flags, self::UNMATCHED_NULL_FLAG)) {
            return null;
        }
        $node = $this->handleEmptyStringToNullMatch($node, $variable);
        unset($node->args[3]);
        return $node;
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
            $classConst->consts[$key]->value = new LNumber(512);
            return $classConst;
        }
        return null;
    }
    private function handleEmptyStringToNullMatch(FuncCall $funcCall, Variable $variable) : FuncCall
    {
        $closure = new Closure();
        $variablePass = new Variable('value');
        $param = new Param($variablePass);
        $param->byRef = \true;
        $closure->params = [$param];
        $assign = new Assign($variablePass, $this->nodeFactory->createNull());
        $if = $this->ifManipulator->createIfStmt(new Identical($variablePass, new String_('')), new Expression($assign));
        $closure->stmts[0] = $if;
        $arguments = $this->nodeFactory->createArgs([$variable, $closure]);
        $replaceEmptyStringToNull = $this->nodeFactory->createFuncCall('array_walk_recursive', $arguments);
        return $this->processReplace($funcCall, $replaceEmptyStringToNull);
    }
    private function processReplace(FuncCall $funcCall, FuncCall $replaceEmptyStringToNull) : FuncCall
    {
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Expression) {
            $this->nodesToAddCollector->addNodeAfterNode($replaceEmptyStringToNull, $funcCall);
            return $funcCall;
        }
        if ($parentNode instanceof If_ && $parentNode->cond === $funcCall) {
            return $this->processInIf($parentNode, $funcCall, $replaceEmptyStringToNull);
        }
        if (!$parentNode instanceof Node) {
            throw new NotImplementedException();
        }
        $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof BooleanNot && $parentParentNode instanceof If_) {
            return $this->processInIf($parentParentNode, $funcCall, $replaceEmptyStringToNull);
        }
        if ($parentNode instanceof Assign && $parentNode->expr === $funcCall) {
            return $this->processInAssign($parentNode, $funcCall, $replaceEmptyStringToNull);
        }
        if (!$parentNode instanceof Identical) {
            throw new NotImplementedYetException();
        }
        if (!$parentParentNode instanceof If_) {
            throw new NotImplementedYetException();
        }
        return $this->processInIf($parentParentNode, $funcCall, $replaceEmptyStringToNull);
    }
    private function processInAssign(Assign $assign, FuncCall $funcCall, FuncCall $replaceEmptyStringToNull) : FuncCall
    {
        $this->nodesToAddCollector->addNodeAfterNode(new Expression($replaceEmptyStringToNull), $assign);
        return $funcCall;
    }
    private function processInIf(If_ $if, FuncCall $funcCall, FuncCall $replaceEmptyStringToNull) : FuncCall
    {
        $cond = $if->cond;
        if (!$cond instanceof Identical && !$cond instanceof BooleanNot) {
            $this->handleNotInIdenticalAndBooleanNot($if, $replaceEmptyStringToNull);
        }
        if ($cond instanceof Identical) {
            $valueCompare = $cond->left === $funcCall ? $cond->right : $cond->left;
            if ($this->valueResolver->isFalse($valueCompare)) {
                $this->nodesToAddCollector->addNodeAfterNode($replaceEmptyStringToNull, $if);
            }
        }
        if ($cond instanceof BooleanNot) {
            $this->nodesToAddCollector->addNodeAfterNode($replaceEmptyStringToNull, $if);
        }
        return $funcCall;
    }
    private function handleNotInIdenticalAndBooleanNot(If_ $if, FuncCall $funcCall) : void
    {
        if ($if->stmts !== []) {
            $firstStmt = $if->stmts[0];
            $this->nodesToAddCollector->addNodeBeforeNode($funcCall, $firstStmt);
            return;
        }
        $if->stmts[0] = new Expression($funcCall);
    }
}
