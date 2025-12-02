<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\NotEqual;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\NotEqual\CommonNotEqualRector\CommonNotEqualRectorTest
 */
final class CommonNotEqualRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use common != instead of less known <> with same meaning', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one, $two)
    {
        return $one <> $two;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($one, $two)
    {
        return $one != $two;
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
        return [NotEqual::class];
    }
    /**
     * @param NotEqual $node
     */
    public function refactor(Node $node): ?NotEqual
    {
        $tokenStartPos = $node->getStartTokenPos();
        $tokenEndPos = $node->getEndTokenPos();
        for ($i = $tokenStartPos; $i < $tokenEndPos; ++$i) {
            $token = $this->file->getOldTokens()[$i];
            if ((string) $token === '<>') {
                $token->text = '!=';
                return $node;
            }
        }
        return null;
    }
}
