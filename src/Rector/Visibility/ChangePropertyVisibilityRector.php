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

    public function getNodeType(): string
    {
        return Property::class;
    }

    /**
     * @param Property $propertyNode
     */
    public function refactor(Node $propertyNode): ?Node
    {
        if (! $propertyNode instanceof Property) {
            return null;
        }
        // doesn't have a parent class
        if (! $propertyNode->hasAttribute(Attribute::PARENT_CLASS_NAME)) {
            return null;
        }
        // @todo or better types?
        $nodeParentClassName = $propertyNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (! isset($this->propertyToVisibilityByClass[$nodeParentClassName])) {
            return null;
        }
        $propertyProperty = $propertyNode->props[0];
        $propertyName = $propertyProperty->name->toString();
        if (isset($this->propertyToVisibilityByClass[$nodeParentClassName][$propertyName]) === false) {
            return null;
        }
        $this->visibilityModifier->removeOriginalVisibilityFromFlags($propertyNode);

        $newVisibility = $this->resolveNewVisibilityForNode($propertyNode);
        $this->visibilityModifier->addVisibilityFlag($propertyNode, $newVisibility);

        return $propertyNode;
    }

    private function resolveNewVisibilityForNode(Property $propertyNode): string
    {
        $nodeParentClassName = $propertyNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        $propertyName = $propertyNode->props[0]->name->toString();

        return $this->propertyToVisibilityByClass[$nodeParentClassName][$propertyName];
    }
}
