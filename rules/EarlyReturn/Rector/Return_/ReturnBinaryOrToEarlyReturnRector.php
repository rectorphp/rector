<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\Rector\Return_;

use PhpParser\Node;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as splitting a single return into multiple early returns makes the code longer and harder to read.
 */
final class ReturnBinaryOrToEarlyReturnRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change single return of `||` to early returns', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function accept()
    {
        return $this->something() || $this->somethingElse();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function accept()
    {
        if ($this->something()) {
            return true;
        }
        return (bool) $this->somethingElse();
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
        return NodeGroup::STMTS_AWARE;
    }
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as splitting a single return into multiple early returns makes the code longer and harder to read', self::class));
    }
}
