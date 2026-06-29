<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Switch_\SwitchTrueToMatchRector\SwitchTrueToMatchRectorTest
 */
final class SwitchTrueToMatchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `switch (true)` of returning cases to `match (true)` expression', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        switch (true) {
            case $value === 0:
                return 'no';
            case $value === 1:
                return 'yes';
            default:
                return 'maybe';
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return match (true) {
            $value === 0 => 'no',
            $value === 1 => 'yes',
            default => 'maybe',
        };
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
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
        if (!$this->valueResolver->isTrue($node->cond)) {
            return null;
        }
        if ($node->cases === []) {
            return null;
        }
        $matchArms = [];
        $hasDefault = \false;
        $lastCaseKey = array_key_last($node->cases);
        foreach ($node->cases as $key => $case) {
            // a match arm can only carry a single return expression
            if (count($case->stmts) !== 1) {
                return null;
            }
            $onlyStmt = $case->stmts[0];
            if (!$onlyStmt instanceof Return_) {
                return null;
            }
            if (!$onlyStmt->expr instanceof Expr) {
                return null;
            }
            if ($case->cond instanceof Expr) {
                $matchArms[] = new MatchArm([$case->cond], $onlyStmt->expr);
                continue;
            }
            // default case must be the last one, as a match default arm catches everything
            if ($key !== $lastCaseKey) {
                return null;
            }
            $hasDefault = \true;
            $matchArms[] = new MatchArm(null, $onlyStmt->expr);
        }
        // require a default arm, otherwise match (true) throws UnhandledMatchError where switch (true) falls through
        if (!$hasDefault) {
            return null;
        }
        $match = new Match_($this->nodeFactory->createTrue(), $matchArms);
        return new Return_($match);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::MATCH_EXPRESSION;
    }
}
