<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/match_expression_v2
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector\DowngradeMatchToSwitchRectorTest
 */
final class DowngradeMatchToSwitchRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    public function __construct(\Rector\Php72\NodeFactory\AnonymousFunctionFactory $anonymousFunctionFactory)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade match() to switch()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $message = match ($statusCode) {
            200, 300 => null,
            400 => 'not found',
            default => 'unknown status code',
        };
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        switch ($statusCode) {
            case 200:
            case 300:
                $message = null;
                break;
            case 400:
                $message = 'not found';
                break;
            default:
                $message = 'unknown status code';
                break;
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Echo_::class, \PhpParser\Node\Stmt\Expression::class, \PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Echo_|Expression|Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $match = $this->betterNodeFinder->findFirst($node, function (\PhpParser\Node $subNode) : bool {
            return $subNode instanceof \PhpParser\Node\Expr\Match_;
        });
        if (!$match instanceof \PhpParser\Node\Expr\Match_) {
            return null;
        }
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($match);
        if ($currentStmt !== $node) {
            return null;
        }
        $switchCases = $this->createSwitchCasesFromMatchArms($node, $match);
        $switch = new \PhpParser\Node\Stmt\Switch_($match->cond, $switchCases);
        $parentMatch = $match->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parentMatch instanceof \PhpParser\Node\Expr\ArrowFunction) {
            return $this->refactorInArrowFunction($parentMatch, $match, $node);
        }
        if ($parentMatch instanceof \PhpParser\Node\Expr\ArrayItem) {
            $parentMatch->value = new \PhpParser\Node\Expr\FuncCall($this->anonymousFunctionFactory->create([], [$switch], null));
            return $node;
        }
        return $switch;
    }
    /**
     * @param \PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $node
     * @return \PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_|null
     */
    private function refactorInArrowFunction(\PhpParser\Node\Expr\ArrowFunction $arrowFunction, \PhpParser\Node\Expr\Match_ $match, $node)
    {
        $parentOfParentMatch = $arrowFunction->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentOfParentMatch instanceof \PhpParser\Node) {
            return null;
        }
        /**
         * Yes, Pass Match_ object itself to Return_
         * Let the Rule revisit the Match_ after the ArrowFunction converted to Closure_
         */
        $stmts = [new \PhpParser\Node\Stmt\Return_($match)];
        $closure = $this->anonymousFunctionFactory->create($arrowFunction->params, $stmts, $arrowFunction->returnType, $arrowFunction->static);
        if ($parentOfParentMatch instanceof \PhpParser\Node\Arg && $parentOfParentMatch->value === $arrowFunction) {
            $parentOfParentMatch->value = $closure;
            return $node;
        }
        if (($parentOfParentMatch instanceof \PhpParser\Node\Expr\Assign || $parentOfParentMatch instanceof \PhpParser\Node\Stmt\Expression || $parentOfParentMatch instanceof \PhpParser\Node\Stmt\Return_) && $parentOfParentMatch->expr === $arrowFunction) {
            $parentOfParentMatch->expr = $closure;
            return $node;
        }
        if ($parentOfParentMatch instanceof \PhpParser\Node\Expr\FuncCall && $parentOfParentMatch->name === $arrowFunction) {
            $parentOfParentMatch->name = $closure;
            return $node;
        }
        return null;
    }
    /**
     * @return Case_[]
     * @param \PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $node
     */
    private function createSwitchCasesFromMatchArms($node, \PhpParser\Node\Expr\Match_ $match) : array
    {
        $switchCases = [];
        $parentNode = $match->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        foreach ($match->arms as $matchArm) {
            if (\count((array) $matchArm->conds) > 1) {
                $lastCase = null;
                foreach ((array) $matchArm->conds as $matchArmCond) {
                    $lastCase = new \PhpParser\Node\Stmt\Case_($matchArmCond);
                    $switchCases[] = $lastCase;
                }
                if (!$lastCase instanceof \PhpParser\Node\Stmt\Case_) {
                    throw new \Rector\Core\Exception\ShouldNotHappenException();
                }
                $lastCase->stmts = $this->createSwitchStmts($node, $matchArm, $parentNode);
            } else {
                $stmts = $this->createSwitchStmts($node, $matchArm, $parentNode);
                $switchCases[] = new \PhpParser\Node\Stmt\Case_($matchArm->conds[0] ?? null, $stmts);
            }
        }
        return $switchCases;
    }
    /**
     * @return Stmt[]
     * @param \PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $node
     */
    private function createSwitchStmts($node, \PhpParser\Node\MatchArm $matchArm, ?\PhpParser\Node $parentNode) : array
    {
        $stmts = [];
        if ($parentNode instanceof \PhpParser\Node\Expr\ArrayItem) {
            $stmts[] = new \PhpParser\Node\Stmt\Return_($matchArm->body);
        } elseif ($matchArm->body instanceof \PhpParser\Node\Expr\Throw_) {
            $stmts[] = new \PhpParser\Node\Stmt\Expression($matchArm->body);
        } elseif ($node instanceof \PhpParser\Node\Stmt\Return_) {
            $stmts[] = new \PhpParser\Node\Stmt\Return_($matchArm->body);
        } elseif ($node instanceof \PhpParser\Node\Stmt\Echo_) {
            $stmts[] = new \PhpParser\Node\Stmt\Echo_([$matchArm->body]);
            $stmts[] = new \PhpParser\Node\Stmt\Break_();
        } elseif ($node->expr instanceof \PhpParser\Node\Expr\Assign) {
            $stmts[] = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($node->expr->var, $matchArm->body));
            $stmts[] = new \PhpParser\Node\Stmt\Break_();
        } elseif ($node->expr instanceof \PhpParser\Node\Expr\Match_) {
            $stmts[] = new \PhpParser\Node\Stmt\Expression($matchArm->body);
            $stmts[] = new \PhpParser\Node\Stmt\Break_();
        } elseif ($node->expr instanceof \PhpParser\Node\Expr\CallLike) {
            /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $call */
            $call = clone $node->expr;
            $call->args = [new \PhpParser\Node\Arg($matchArm->body)];
            $stmts[] = new \PhpParser\Node\Stmt\Expression($call);
            $stmts[] = new \PhpParser\Node\Stmt\Break_();
        }
        return $stmts;
    }
}
