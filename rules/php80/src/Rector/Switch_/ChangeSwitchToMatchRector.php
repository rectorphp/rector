<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/match_expression_v2
 *
 * @see \Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\ChangeSwitchToMatchRectorTest
 */
final class ChangeSwitchToMatchRector extends AbstractRector
{
    /**
     * @var Expr|null
     */
    private $assignExpr;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change switch() to match()', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $statement = switch ($this->lexer->lookahead['type']) {
            case Lexer::T_SELECT:
                $statement = $this->SelectStatement();
                break;

            case Lexer::T_UPDATE:
                $statement = $this->UpdateStatement();
                break;

            case Lexer::T_DELETE:
                $statement = $this->DeleteStatement();
                break;

            default:
                $this->syntaxError('SELECT, UPDATE or DELETE');
                break;
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $statement = match ($this->lexer->lookahead['type']) {
            Lexer::T_SELECT => $this->SelectStatement(),
            Lexer::T_UPDATE => $this->UpdateStatement(),
            Lexer::T_DELETE => $this->DeleteStatement(),
            default => $this->syntaxError('SELECT, UPDATE or DELETE'),
        };
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Switch_::class];
    }

    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->assignExpr = null;

        if (! $this->hasEachCaseBreak($node)) {
            return null;
        }

        if (! $this->hasSingleStmtCases($node)) {
            return null;
        }

        if (! $this->hasSingleAssignVariableInStmtCase($node)) {
            return null;
        }

        $matchArms = $this->createMatchArmsFromCases($node->cases);
        $match = new Match_($node->cond, $matchArms);
        if ($this->assignExpr) {
            return new Assign($this->assignExpr, $match);
        }

        return $match;
    }

    private function hasEachCaseBreak(Switch_ $switch): bool
    {
        foreach ($switch->cases as $case) {
            foreach ($case->stmts as $caseStmt) {
                if (! $caseStmt instanceof Break_) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    private function hasSingleStmtCases(Switch_ $switch): bool
    {
        foreach ($switch->cases as $case) {
            $stmtsWithoutBreak = array_filter($case->stmts, function (Node $node): bool {
                return ! $node instanceof Break_;
            });

            if (count($stmtsWithoutBreak) !== 1) {
                return false;
            }
        }

        return true;
    }

    private function hasSingleAssignVariableInStmtCase(Switch_ $switch): bool
    {
        $assignVariableNames = [];

        foreach ($switch->cases as $case) {
            /** @var Expression $onlyStmt */
            $onlyStmt = $case->stmts[0];
            $expr = $onlyStmt->expr;

            if (! $expr instanceof Assign) {
                continue;
            }

            $assignVariableNames[] = $this->getName($expr->var);
        }

        $assignVariableNames = array_unique($assignVariableNames);

        return count($assignVariableNames) <= 1;
    }

    /**
     * @param Case_[] $cases
     * @return MatchArm[]
     */
    private function createMatchArmsFromCases(array $cases): array
    {
        $matchArms = [];
        foreach ($cases as $case) {
            $stmt = $case->stmts[0];
            if (! $stmt instanceof Expression) {
                throw new ShouldNotHappenException();
            }

            $expr = $stmt->expr;

            if ($expr instanceof Assign) {
                $this->assignExpr = $expr->var;
                $expr = $expr->expr;
            }

            $condList = $case->cond === null ? null : [$case->cond];
            $matchArms[] = new MatchArm($condList, $expr);
        }

        return $matchArms;
    }
}
