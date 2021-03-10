<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\Case_;

use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Continue_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php73\Tests\Rector\Case_\ConvertContinueToBreakRector\ConvertContinueToBreakRectorTest
 */
final class ConvertContinueToBreakRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces continue statement with break',
            [
                new CodeSample(
                    <<<'GOOD_SWITCH'
switch(true) {
    case 1:
        continue;
}
GOOD_SWITCH,
                    <<<'BAD_SWITCH'
switch(true) {
    case 1:
        break;
}
BAD_SWITCH,
                )
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [
            Case_::class
        ];
    }

    /**
     * @param Case_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $statements = $node->stmts;

        foreach ($statements as $i => $statement) {
            if ($this->shouldReplaceWithBreak($statement)) {
                $statements[$i] = new Break_();
            }
        }

        $node->stmts = $statements;
        return $node;
    }

    private function shouldReplaceWithBreak(Stmt $node): bool
    {
        if (!$node instanceof Continue_) {
            return false;
        }

        // Plain `continue;` should be replaced
        if ($node->num === null) {
            return true;
        }

        // Only replace `continue 0;` and `continue 1;`
        /** @var LNumber $num */
        $num = $node->num;
        return $num->value < 2;
    }
}
