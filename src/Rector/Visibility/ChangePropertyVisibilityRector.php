<?php declare(strict_types=1);

namespace Rector\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\NodeModifier\VisibilityModifier;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ChangePropertyVisibilityRector extends AbstractRector
{
    /**
     * @var string[] { class => [ property name => visibility ] }
     */
    private $propertyToVisibilityByClass = [];

    /**
     * @var VisibilityModifier
     */
    private $visibilityModifier;

    /**
     * @param string[] $propertyToVisibilityByClass
     */
    public function __construct(array $propertyToVisibilityByClass, VisibilityModifier $visibilityModifier)
    {
        $this->propertyToVisibilityByClass = $propertyToVisibilityByClass;
        $this->visibilityModifier = $visibilityModifier;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change visibility of property from parent class.',
            [new ConfiguredCodeSample(
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
                    '$propertyToVisibilityByClass' => [
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
        // doesn't have a parent class
        if (! $node->hasAttribute(Attribute::PARENT_CLASS_NAME)) {
            return null;
        }

        $nodeParentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (! isset($this->propertyToVisibilityByClass[$nodeParentClassName])) {
            return null;
        }

        $propertyName = $this->getName($node);
        if (! isset($this->propertyToVisibilityByClass[$nodeParentClassName][$propertyName])) {
            return null;
        }
        $this->visibilityModifier->removeOriginalVisibilityFromFlags($node);

        $newVisibility = $this->resolveNewVisibilityForNode($node);
        $this->visibilityModifier->addVisibilityFlag($node, $newVisibility);

        return $node;
    }

    private function resolveNewVisibilityForNode(Property $propertyNode): string
    {
        /** @var string $nodeParentClassName */
        $nodeParentClassName = $propertyNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        /** @var string $propertyName */
        $propertyName = $this->getName($propertyNode);

        return $this->propertyToVisibilityByClass[$nodeParentClassName][$propertyName];
    }
}
