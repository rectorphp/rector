<?php

declare(strict_types=1);

namespace Rector\Visibility\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Visibility;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Visibility\Tests\Rector\Property\ChangePropertyVisibilityRector\ChangePropertyVisibilityRectorTest
 */
final class ChangePropertyVisibilityRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PROPERTY_TO_VISIBILITY_BY_CLASS = 'property_to_visibility_by_class';

    /**
     * @var array<string, array<string, int>>
     */
    private $propertyToVisibilityByClass = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change visibility of property from parent class.',
            [
                new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class FrameworkClass
{
    protected $someProperty;
}

class MyClass extends FrameworkClass
{
    public $someProperty;
}
CODE_SAMPLE
                                ,
                <<<'CODE_SAMPLE'
class FrameworkClass
{
    protected $someProperty;
}

class MyClass extends FrameworkClass
{
    protected $someProperty;
}
CODE_SAMPLE
                                ,
                                [
                                    self::PROPERTY_TO_VISIBILITY_BY_CLASS => [
                                        'FrameworkClass' => [
                                            'someProperty' => Visibility::PROTECTED,
                                        ],
                                    ],
                                ]
                            ),
            ]
        );
    }

    /**
     * @return string[]
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
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        foreach ($this->propertyToVisibilityByClass as $type => $propertyToVisibility) {
            if (! $this->isObjectType($classLike, $type)) {
                continue;
            }

            foreach ($propertyToVisibility as $property => $visibility) {
                if (! $this->isName($node, $property)) {
                    continue;
                }

                $this->visibilityManipulator->changeNodeVisibility($node, $visibility);

                return $node;
            }
        }

        return null;
    }

    public function configure(array $configuration): void
    {
        $this->propertyToVisibilityByClass = $configuration[self::PROPERTY_TO_VISIBILITY_BY_CLASS] ?? [];
    }
}
