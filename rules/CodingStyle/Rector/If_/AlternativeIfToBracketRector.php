<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use PhpParser\Token;
use Rector\Contract\Rector\HTMLAverseRectorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\If_\AlternativeIfToBracketRector\AlternativeIfToBracketRectorTest
 */
final class AlternativeIfToBracketRector extends AbstractRector implements HTMLAverseRectorInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change alternative if/elseif/else/endif syntax to bracket syntax', [new CodeSample(<<<'CODE_SAMPLE'
if ($value) :
    $result = 1;
else :
    $result = 2;
endif;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if ($value) {
    $result = 1;
} else {
    $result = 2;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isAlternativeSyntax($node)) {
            return null;
        }
        // force reprint of the whole if/elseif/else chain with bracket syntax
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        foreach ($node->elseifs as $elseif) {
            $elseif->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        if ($node->else instanceof Node) {
            $node->else->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        return $node;
    }
    private function isAlternativeSyntax(If_ $if): bool
    {
        $startTokenPos = $if->cond->getEndTokenPos();
        if ($startTokenPos < 0) {
            return \false;
        }
        $oldTokens = $this->getFile()->getOldTokens();
        for ($i = $startTokenPos + 1; isset($oldTokens[$i]); ++$i) {
            $token = $oldTokens[$i];
            if (!$token instanceof Token) {
                continue;
            }
            if ($token->text === ':') {
                return \true;
            }
            if ($token->text === '{') {
                return \false;
            }
        }
        return \false;
    }
}
