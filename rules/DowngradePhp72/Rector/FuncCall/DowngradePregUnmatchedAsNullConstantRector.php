<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FuncCall;

use Nette\NotImplementedException;
use PhpParser\Node;
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
final class DowngradePregUnmatchedAsNullConstantRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const REGEX_FUNCTION_NAMES = ['preg_match', 'preg_match_all'];

    /**
     * @var string
     */
    private const FLAG = 'PREG_UNMATCHED_AS_NULL';

    public function __construct(
        private IfManipulator $ifManipulator
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, ClassConst::class];
    }

    /**
     * @param FuncCall|ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassConst) {
            return $this->processsClassConst($node);
        }

        if (! $this->isRegexFunctionNames($node)) {
            return null;
        }

        $args = $node->args;
        if (! isset($args[3])) {
            return null;
        }

        $flags = $args[3]->value;
        /** @var Variable $variable */
        $variable = $args[2]->value;

        if ($flags instanceof BitwiseOr) {
            $this->cleanBitWiseOrFlags($node, $flags);
            if (! $this->nodeComparator->areNodesEqual($flags, $node->args[3]->value)) {
                return $this->handleEmptyStringToNullMatch($node, $variable);
            }

            return null;
        }

        if (! $flags instanceof ConstFetch) {
            return null;
        }

        if (! $this->isName($flags, self::FLAG)) {
            return null;
        }

        $node = $this->handleEmptyStringToNullMatch($node, $variable);
        unset($node->args[3]);

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove PREG_UNMATCHED_AS_NULL from preg_match and set null value on empty string matched on each match',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('/(a)(b)*(c)/', 'ac', $matches, PREG_UNMATCHED_AS_NULL);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    private function processsClassConst(ClassConst $classConst): ClassConst
    {
        foreach ($classConst->consts as $key => $singleClassConst) {
            if (! $singleClassConst->value instanceof ConstFetch) {
                continue;
            }
            if (! $this->isName($singleClassConst->value, self::FLAG)) {
                continue;
            }
            $classConst->consts[$key]->value = new LNumber(512);
            return $classConst;
        }

        return $classConst;
    }

    private function isRegexFunctionNames(FuncCall $funcCall): bool
    {
        if ($this->isNames($funcCall, self::REGEX_FUNCTION_NAMES)) {
            return true;
        }

        $variable = $funcCall->name;
        if (! $variable instanceof Variable) {
            return false;
        }

        /** @var Assign|null $assignExprVariable */
        $assignExprVariable = $this->betterNodeFinder->findFirstPreviousOfNode($funcCall, function (Node $node) use (
            $variable
        ): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            return $this->nodeComparator->areNodesEqual($node->var, $variable);
        });

        if (! $assignExprVariable instanceof Assign) {
            return false;
        }

        $expr = $assignExprVariable->expr;
        if (! $expr instanceof Ternary) {
            return false;
        }

        if (! $expr->if instanceof String_) {
            return false;
        }

        if (! $expr->else instanceof String_) {
            return false;
        }

        return in_array($expr->if->value, self::REGEX_FUNCTION_NAMES, true) && in_array(
            $expr->else->value,
            self::REGEX_FUNCTION_NAMES,
            true
        );
    }

    private function cleanBitWiseOrFlags(FuncCall $funcCall, BitwiseOr $bitwiseOr, ?Expr $expr = null): void
    {
        if ($bitwiseOr->left instanceof BitwiseOr) {
            /** @var BitwiseOr $leftLeft */
            $leftLeft = $bitwiseOr->left;
            if ($leftLeft->left instanceof ConstFetch && $this->isName($leftLeft->left, self::FLAG)) {
                $bitwiseOr = new BitwiseOr($leftLeft->right, $bitwiseOr->right);
            }

            /** @var BitwiseOr $leftRight */
            $leftRight = $bitwiseOr->left;
            if ($leftRight->right instanceof ConstFetch && $this->isName($leftRight->right, self::FLAG)) {
                $bitwiseOr = new BitwiseOr($leftRight->left, $bitwiseOr->right);
            }

            if ($bitwiseOr->left instanceof BitwiseOr) {
                $this->cleanBitWiseOrFlags($funcCall, $bitwiseOr->left, $bitwiseOr->right);
                return;
            }
        }

        if ($expr instanceof Expr) {
            $bitwiseOr = new BitwiseOr($bitwiseOr, $expr);
        }

        $this->assignThirdArgsValue($funcCall, $bitwiseOr);
    }

    private function assignThirdArgsValue(FuncCall $funcCall, BitwiseOr $bitwiseOr): void
    {
        if ($bitwiseOr instanceof BitWiseOr && $bitwiseOr->right instanceof ConstFetch && $this->isName(
            $bitwiseOr->right,
            self::FLAG
        )) {
            $bitwiseOr = $bitwiseOr->left;
        }

        if ($bitwiseOr instanceof BitWiseOr && $bitwiseOr->left instanceof ConstFetch && $this->isName(
            $bitwiseOr->left,
            self::FLAG
        )) {
            $bitwiseOr = $bitwiseOr->right;
        }

        $funcCall->args[3]->value = $bitwiseOr;
    }

    private function handleEmptyStringToNullMatch(FuncCall $funcCall, Variable $variable): FuncCall
    {
        $closure = new Closure();
        $variablePass = new Variable('value');
        $param = new Param($variablePass);
        $param->byRef = true;
        $closure->params = [$param];

        $assign = new Assign($variablePass, $this->nodeFactory->createNull());

        $if = $this->ifManipulator->createIfExpr(
            new Identical($variablePass, new String_('')),
            new Expression($assign)
        );

        $closure->stmts[0] = $if;

        $arguments = $this->nodeFactory->createArgs([$variable, $closure]);
        $replaceEmptystringToNull = $this->nodeFactory->createFuncCall('array_walk_recursive', $arguments);

        return $this->processReplace($funcCall, $replaceEmptystringToNull);
    }

    private function processReplace(FuncCall $funcCall, FuncCall $replaceEmptystringToNull): FuncCall
    {
        $parent = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Expression) {
            $this->addNodeAfterNode($replaceEmptystringToNull, $funcCall);
            return $funcCall;
        }

        if ($parent instanceof If_ && $parent->cond === $funcCall) {
            return $this->processInIf($parent, $funcCall, $replaceEmptystringToNull);
        }

        if (! $parent instanceof Node) {
            throw new NotImplementedException();
        }

        $if = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof BooleanNot) {
            return $this->processInIf($if, $funcCall, $replaceEmptystringToNull);
        }

        if (! $parent instanceof Identical) {
            throw new NotImplementedYetException();
        }

        if (! $if instanceof If_) {
            throw new NotImplementedYetException();
        }

        return $this->processInIf($if, $funcCall, $replaceEmptystringToNull);
    }

    private function processInIf(If_ $if, FuncCall $funcCall, FuncCall $replaceEmptystringToNull): FuncCall
    {
        $cond = $if->cond;

        if (! $cond instanceof Identical && ! $cond instanceof BooleanNot) {
            $this->handleNotInIdenticalAndBooleanNot($if, $replaceEmptystringToNull);
        }

        if ($cond instanceof Identical) {
            $valueCompare = $cond->left === $funcCall
                ? $cond->right
                : $cond->left;
            if ($this->valueResolver->isFalse($valueCompare)) {
                $this->addNodeAfterNode($replaceEmptystringToNull, $if);
            }
        }

        if ($cond instanceof BooleanNot) {
            $this->addNodeAfterNode($replaceEmptystringToNull, $if);
        }

        return $funcCall;
    }

    private function handleNotInIdenticalAndBooleanNot(If_ $if, FuncCall $funcCall): void
    {
        if ($if->stmts !== []) {
            $firstStmt = $if->stmts[0];
            $this->addNodeBeforeNode($funcCall, $firstStmt);
        } else {
            $if->stmts[0] = new Expression($funcCall);
        }
    }
}
