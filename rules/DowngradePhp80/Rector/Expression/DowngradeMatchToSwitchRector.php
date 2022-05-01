<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayItem;
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
        return [\PhpParser\Node\Expr\ArrayItem::class, \PhpParser\Node\Stmt\Echo_::class, \PhpParser\Node\Stmt\Expression::class, \PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param ArrayItem|Echo_|Expression|Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipNode($node)) {
            return null;
        }
        $match = $this->betterNodeFinder->findFirst($node, function (\PhpParser\Node $subNode) : bool {
            return $subNode instanceof \PhpParser\Node\Expr\Match_;
        });
        if (!$match instanceof \PhpParser\Node\Expr\Match_) {
            return null;
        }
        if ($this->shouldSkipMatch($match)) {
            return null;
        }
        $switchCases = $this->createSwitchCasesFromMatchArms($node, $match->arms);
        $switch = new \PhpParser\Node\Stmt\Switch_($match->cond, $switchCases);
        if ($node instanceof \PhpParser\Node\Expr\ArrayItem) {
            $node->value = new \PhpParser\Node\Expr\FuncCall($this->anonymousFunctionFactory->create([], [$switch], null));
            return $node;
        }
        return $switch;
    }
    private function shouldSkipNode(\PhpParser\Node $node) : bool
    {
        return $node instanceof \PhpParser\Node\Stmt\Return_ && !$node->expr instanceof \PhpParser\Node\Expr\Match_;
    }
    private function shouldSkipMatch(\PhpParser\Node\Expr\Match_ $match) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($match, function (\PhpParser\Node $subNode) : bool {
            return $subNode instanceof \PhpParser\Node\Expr\ArrayItem && $subNode->unpack;
        });
    }
    /**
     * @param MatchArm[] $matchArms
     * @return Case_[]
     * @param \PhpParser\Node\Expr\ArrayItem|\PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $node
     */
    private function createSwitchCasesFromMatchArms($node, array $matchArms) : array
    {
        $switchCases = [];
        foreach ($matchArms as $matchArm) {
            if (\count((array) $matchArm->conds) > 1) {
                $lastCase = null;
                foreach ((array) $matchArm->conds as $matchArmCond) {
                    $lastCase = new \PhpParser\Node\Stmt\Case_($matchArmCond);
                    $switchCases[] = $lastCase;
                }
                if (!$lastCase instanceof \PhpParser\Node\Stmt\Case_) {
                    throw new \Rector\Core\Exception\ShouldNotHappenException();
                }
                $lastCase->stmts = $this->createSwitchStmts($node, $matchArm);
            } else {
                $stmts = $this->createSwitchStmts($node, $matchArm);
                $switchCases[] = new \PhpParser\Node\Stmt\Case_($matchArm->conds[0] ?? null, $stmts);
            }
        }
        return $switchCases;
    }
    /**
     * @return Stmt[]
     * @param \PhpParser\Node\Expr\ArrayItem|\PhpParser\Node\Stmt\Echo_|\PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Return_ $node
     */
    private function createSwitchStmts($node, \PhpParser\Node\MatchArm $matchArm) : array
    {
        $stmts = [];
        if ($matchArm->body instanceof \PhpParser\Node\Expr\Throw_) {
            $stmts[] = new \PhpParser\Node\Stmt\Expression($matchArm->body);
        } elseif ($node instanceof \PhpParser\Node\Expr\ArrayItem || $node instanceof \PhpParser\Node\Stmt\Return_) {
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
