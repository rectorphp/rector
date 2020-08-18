<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\Property\ChangePropertyVisibilityRector\ChangePropertyVisibilityRectorTest
 */
final class ChangePropertyVisibilityRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PROPERTY_TO_VISIBILITY_BY_CLASS = 'propertyToVisibilityByClass';

    /**
     * @var string[][] { class => [ property name => visibility ] }
     */
    private $propertyToVisibilityByClass = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change visibility of property from parent class.',
            [new ConfiguredCodeSample(
<<<'PHP'
class FrameworkClass
{
    protected $someProperty;
}

class MyClass extends FrameworkClass
{
    public $someProperty;
}
PHP
                ,
<<<'PHP'
class FrameworkClass
{
    protected $someProperty;
}

class MyClass extends FrameworkClass
{
    protected $someProperty;
}
PHP
                ,
                [
                    self::PROPERTY_TO_VISIBILITY_BY_CLASS => [
                        'FrameworkClass' => [
                            'someProperty' => 'protected',
                        ],
                    ],
                ]
            )]
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
        foreach ($this->propertyToVisibilityByClass as $type => $propertyToVisibility) {
            $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
            if ($classNode === null) {
                continue;
            }

            if (! $this->isObjectType($classNode, $type)) {
                continue;
            }

            foreach ($propertyToVisibility as $property => $visibility) {
                if (! $this->isName($node, $property)) {
                    continue;
                }

                $this->changeNodeVisibility($node, $visibility);

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
