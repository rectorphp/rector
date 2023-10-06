<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\CompleteMissingIfElseBracketRector\CompleteMissingIfElseBracketRectorTest
 */
final class CompleteMissingIfElseBracketRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Complete missing if/else brackets', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value)
            return 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return 1;
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
        return [If_::class, ElseIf_::class, Else_::class];
    }
    /**
     * @param If_|ElseIf_|Else_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->isBareNewNode($node)) {
            return null;
        }
        $oldTokens = $this->file->getOldTokens();
        if ($this->shouldSkip($node, $oldTokens)) {
            return null;
        }
        // invoke reprint with brackets
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
    /**
     * @param mixed[] $oldTokens
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\ElseIf_|\PhpParser\Node\Stmt\Else_ $if
     */
    private function shouldSkip($if, array $oldTokens) : bool
    {
        for ($i = $if->getStartTokenPos(); $i < $if->getEndTokenPos(); ++$i) {
            if ($oldTokens[$i] === ';') {
                // all good
                return \true;
            }
        }
        $startStmt = \current($if->stmts);
        $lastStmt = \end($if->stmts);
        return $startStmt === \false || $lastStmt === \false;
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\ElseIf_|\PhpParser\Node\Stmt\Else_ $if
     */
    private function isBareNewNode($if) : bool
    {
        $originalNode = $if->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalNode instanceof Node) {
            return \true;
        }
        // not defined, probably new if
        return $if->getStartTokenPos() === -1;
    }
}
