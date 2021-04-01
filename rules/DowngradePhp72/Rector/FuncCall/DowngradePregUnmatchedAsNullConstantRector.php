<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Transform\NodeFactory\UnwrapClosureFactory;

/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\DowngradePregUnmatchedAsNullConstantRectorTest
 */
final class DowngradePregUnmatchedAsNullConstantRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const REGEX_FUNCTION_NAMES = [
        'preg_match',
        'preg_match_all',
    ];

    /**
     * @var string
     */
    private const FLAG = 'PREG_UNMATCHED_AS_NULL';

    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var UnwrapClosureFactory
     */
    private $unwrapClosureFactory;

    public function __construct(IfManipulator $ifManipulator, UnwrapClosureFactory $unwrapClosureFactory)
    {
        $this->ifManipulator = $ifManipulator;
        $this->unwrapClosureFactory = $unwrapClosureFactory;
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isNames($node, self::REGEX_FUNCTION_NAMES)) {
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
            $this->cleanBitWiseOrFlags($flags);
            return $this->handleEmptyStringToNullMatch($node, $variable);
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

    private function cleanBitWiseOrFlags(BitwiseOr $bitwiseOr)
    {
        $exprBitWise = [];
        while ($bitwiseOr->left instanceof BitwiseOr) {
            $bitwiseOr->right = $bitwiseOr->left->right;
            $bitwiseOr->left = $bitwiseOr->left->left;

            if ($bitwiseOr->left instanceof ConstFetch && $this->isName($bitwiseOr->left, self::FLAG)) {
                unset($bitwiseOr->left);
            }

            $exprBitWise[] = $bitwiseOr->left;
            $exprBitWise[] = $bitwiseOr->right;
        }
   }

    private function handleEmptyStringToNullMatch(FuncCall $funcCall, Variable $variable): FuncCall
    {
        $closure                  = new Closure();
        $variablePass             = new Variable('value');
        $argClosure               = new Arg($variablePass);
        $argClosure->byRef        = true;
        $closure->params          = [$argClosure];

        $assign = new Assign($variablePass, $this->nodeFactory->createNull());

        $if = $this->ifManipulator->createIfExpr(
            new Identical($variablePass, new String_('')),
            new Expression($assign)
        );

        $closure->stmts[0]  = $if;

        $arguments                = $this->nodeFactory->createArgs([$variable, $closure]);
        $replaceEmptystringToNull = $this->nodeFactory->createFuncCall('array_walk_recursive', $arguments);

        $this->addNodeAfterNode($replaceEmptystringToNull, $funcCall);

        return $funcCall;
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
}
