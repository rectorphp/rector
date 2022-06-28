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
final class DowngradeMatchToSwitchRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    public function __construct(AnonymousFunctionFactory $anonymousFunctionFactory)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade match() to switch()', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Echo_::class, Expression::class, Return_::class];
    }
    /**
     * @param Echo_|Expression|Return_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $match = $this->betterNodeFinder->findFirst($node, static function (Node $subNode) : bool {
            return $subNode instanceof Match_;
        });
        if (!$match instanceof Match_) {
            return null;
        }
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($match);
        if ($currentStmt !== $node) {
            return null;
        }
        $switchCases = $this->createSwitchCasesFromMatchArms($node, $match);
        $switch = new Switch_($match->cond, $switchCases);
        $parentMatch = $match->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentMatch instanceof ArrowFunction) {
            return $this->refactorInArrowFunction($parentMatch, $match, $node);
        }
        if ($parentMatch instanceof ArrayItem) {
            $parentMatch->value = new FuncCall($this->anonymousFunctionFactory->create([], [$switch], null));
            return $node;
        }
        return $switch;
    }
    /**
     * @param \PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $node
     * @return \PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_|null
     */
    private function refactorInArrowFunction(ArrowFunction $arrowFunction, Match_ $match, $node)
    {
        $parentOfParentMatch = $arrowFunction->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentOfParentMatch instanceof Node) {
            return null;
        }
        /**
         * Yes, Pass Match_ object itself to Return_
         * Let the Rule revisit the Match_ after the ArrowFunction converted to Closure_
         */
        $stmts = [new Return_($match)];
        $closure = $this->anonymousFunctionFactory->create($arrowFunction->params, $stmts, $arrowFunction->returnType, $arrowFunction->static);
        if ($parentOfParentMatch instanceof Arg && $parentOfParentMatch->value === $arrowFunction) {
            $parentOfParentMatch->value = $closure;
            return $node;
        }
        if (($parentOfParentMatch instanceof Assign || $parentOfParentMatch instanceof Expression || $parentOfParentMatch instanceof Return_) && $parentOfParentMatch->expr === $arrowFunction) {
            $parentOfParentMatch->expr = $closure;
            return $node;
        }
        if ($parentOfParentMatch instanceof FuncCall && $parentOfParentMatch->name === $arrowFunction) {
            $parentOfParentMatch->name = $closure;
            return $node;
        }
        return null;
    }
    /**
     * @return Case_[]
     * @param \PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $node
     */
    private function createSwitchCasesFromMatchArms($node, Match_ $match) : array
    {
        $switchCases = [];
        $parentNode = $match->getAttribute(AttributeKey::PARENT_NODE);
        foreach ($match->arms as $matchArm) {
            if (\count((array) $matchArm->conds) > 1) {
                $lastCase = null;
                foreach ((array) $matchArm->conds as $matchArmCond) {
                    $lastCase = new Case_($matchArmCond);
                    $switchCases[] = $lastCase;
                }
                /** @var Case_ $lastCase */
                $lastCase->stmts = $this->createSwitchStmts($node, $matchArm, $parentNode);
            } else {
                $stmts = $this->createSwitchStmts($node, $matchArm, $parentNode);
                $switchCases[] = new Case_($matchArm->conds[0] ?? null, $stmts);
            }
        }
        return $switchCases;
    }
    /**
     * @return Stmt[]
     * @param \PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $node
     */
    private function createSwitchStmts($node, MatchArm $matchArm, ?Node $parentNode) : array
    {
        $stmts = [];
        if ($parentNode instanceof ArrayItem) {
            $stmts[] = new Return_($matchArm->body);
        } elseif ($matchArm->body instanceof Throw_) {
            $stmts[] = new Expression($matchArm->body);
        } elseif ($node instanceof Return_) {
            $stmts[] = new Return_($matchArm->body);
        } elseif ($node instanceof Echo_) {
            $stmts[] = new Echo_([$matchArm->body]);
            $stmts[] = new Break_();
        } elseif ($node->expr instanceof Assign) {
            $stmts[] = new Expression(new Assign($node->expr->var, $matchArm->body));
            $stmts[] = new Break_();
        } elseif ($node->expr instanceof Match_) {
            $stmts[] = new Expression($matchArm->body);
            $stmts[] = new Break_();
        } elseif ($node->expr instanceof CallLike) {
            /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $call */
            $call = clone $node->expr;
            $call->args = [new Arg($matchArm->body)];
            $stmts[] = new Expression($call);
            $stmts[] = new Break_();
        }
        return $stmts;
    }
}
