<?php

declare (strict_types=1);
namespace Rector\Symfony\JMS\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as JMS @AccessType annotation to attribute is a niche and rarely used case.
 *
 * @see https://github.com/schmittjoh/serializer/issues/1531
 */
final class AccessTypeAnnotationToAttributeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes @AccessType annotation to #[AccessType] attribute with specific key', [new CodeSample(<<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\AccessType;

/** @AccessType("public_method") */
class User
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\AccessType;

#[AccessType(values: ['public_method'])]
class User
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Property::class];
    }
    /**
     * @param Class_|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as JMS @AccessType annotation to attribute is a niche and rarely used case.', self::class));
    }
}
