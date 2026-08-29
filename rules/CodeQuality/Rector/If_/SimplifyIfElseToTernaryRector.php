<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as the result depends on context and can worsen readability. Extract a method instead if needed.
 */
final class SimplifyIfElseToTernaryRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes if/else for same value as assign to ternary', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if (empty($value)) {
            $this->arrayBuilt[][$key] = true;
        } else {
            $this->arrayBuilt[][$key] = $value;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->arrayBuilt[][$key] = empty($value) ? true : $value;
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as the result depends on context and can worsen readability; extract a method instead if needed', self::class));
    }
}
