<?php

declare (strict_types=1);
namespace Rector\Symfony\JMS\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as JMS @Accessor annotation to attribute is a niche and rarely used case.
 */
final class AccessorAnnotationToAttributeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes @Accessor annotation to #[Accessor] attribute with specific "getter" or "setter" keys', [new CodeSample(<<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\Accessor;

class User
{
    /**
     * @Accessor("getValue")
     */
    private $value;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\Accessor;

class User
{
    #[Accessor(getter: 'getValue')]
    private $value;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as JMS @Accessor annotation to attribute is a niche and rarely used case.', self::class));
    }
}
