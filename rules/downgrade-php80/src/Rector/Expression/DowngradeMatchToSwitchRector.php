<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/match_expression_v2
 *
 * @see \Rector\DowngradePhp80\Tests\Rector\Expression\DowngradeMatchToSwitchRector\DowngradeMatchToSwitchRectorTest
 */
final class DowngradeMatchToSwitchRector extends AbstractRector
{
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
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof Assign) {
            return null;
        }

        $assign = $node->expr;
        if (! $assign->expr instanceof Match_) {
            return null;
        }

        /** @var Match_ $match */
        $match = $assign->expr;

        $switchCases = $this->createSwitchCasesFromMatchArms($match->arms, $assign->var);
        return new Switch_($match->cond, $switchCases);
    }

    /**
     * @param MatchArm[] $matchArms
     * @return Case_[]
     */
    private function createSwitchCasesFromMatchArms(array $matchArms, Expr $assignVarExpr): array
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

                $lastCase->stmts = $this->createSwitchStmts($matchArm, $assignVarExpr);
            } else {
                $stmts = $this->createSwitchStmts($matchArm, $assignVarExpr);
                $switchCases[] = new Case_($matchArm->conds[0] ?? null, $stmts);
            }
        }
        return $switchCases;
    }

    /**
     * @return Stmt[]
     */
    private function createSwitchStmts(MatchArm $matchArm, Expr $assignVarExpr): array
    {
        $stmts = [];

        $stmts[] = new Expression(new Assign($assignVarExpr, $matchArm->body));
        $stmts[] = new Break_();

        return $stmts;
    }
}
