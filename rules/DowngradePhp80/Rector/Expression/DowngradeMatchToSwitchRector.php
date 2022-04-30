<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
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
final class DowngradeMatchToSwitchRector extends AbstractRector
{
    public function __construct(
        private readonly AnonymousFunctionFactory $anonymousFunctionFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade match() to switch()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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

                ,
                <<<'CODE_SAMPLE'
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
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ArrayItem::class, Echo_::class, Expression::class, Return_::class];
    }

    /**
     * @param ArrayItem|Echo_|Expression|Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipNode($node)) {
            return null;
        }

        $match = $this->betterNodeFinder->findFirst($node, fn (Node $subNode): bool => $subNode instanceof Match_);

        if (! $match instanceof Match_ || $this->shouldSkipMatch($match)) {
            return null;
        }

        $switchCases = $this->createSwitchCasesFromMatchArms($node, $match->arms);
        $switch = new Switch_($match->cond, $switchCases);

        if ($node instanceof ArrayItem) {
            $node->value = new FuncCall($this->anonymousFunctionFactory->create([], [$switch], null));
            return $node;
        }

        return $switch;
    }

    private function shouldSkipNode(Node $node): bool
    {
        if ($node instanceof Return_ && ! $node->expr instanceof Match_) {
            return true;
        }

        return false;
    }

    private function shouldSkipMatch(Match_ $match): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $match,
            fn (Node $subNode): bool => $subNode instanceof ArrayItem && $subNode->unpack
        );
    }

    /**
     * @param MatchArm[] $matchArms
     * @return Case_[]
     */
    private function createSwitchCasesFromMatchArms(ArrayItem | Echo_ | Expression | Return_ $node, array $matchArms): array
    {
        $switchCases = [];

        foreach ($matchArms as $matchArm) {
            if (count((array) $matchArm->conds) > 1) {
                $lastCase = null;

                foreach ((array) $matchArm->conds as $matchArmCond) {
                    $lastCase = new Case_($matchArmCond);
                    $switchCases[] = $lastCase;
                }

                if (! $lastCase instanceof Case_) {
                    throw new ShouldNotHappenException();
                }

                $lastCase->stmts = $this->createSwitchStmts($node, $matchArm);
            } else {
                $stmts = $this->createSwitchStmts($node, $matchArm);
                $switchCases[] = new Case_($matchArm->conds[0] ?? null, $stmts);
            }
        }

        return $switchCases;
    }

    /**
     * @return Stmt[]
     */
    private function createSwitchStmts(ArrayItem | Echo_ | Expression | Return_ $node, MatchArm $matchArm): array
    {
        $stmts = [];

        if ($matchArm->body instanceof Throw_) {
            $stmts[] = new Expression($matchArm->body);
        } elseif ($node instanceof ArrayItem || $node instanceof Return_) {
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
