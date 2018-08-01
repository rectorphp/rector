<?php declare(strict_types=1);

namespace Rector\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\NodeModifier\VisibilityModifier;
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

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Property) {
            return false;
        }

        // doesn't have a parent class
        if (! $node->hasAttribute(Attribute::PARENT_CLASS_NAME)) {
            return false;
        }

        // @todo or better types?
        $nodeParentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (! isset($this->propertyToVisibilityByClass[$nodeParentClassName])) {
            return false;
        }

        $propertyProperty = $node->props[0];
        $propertyName = $propertyProperty->name->toString();

        return isset($this->propertyToVisibilityByClass[$nodeParentClassName][$propertyName]);
    }

    /**
     * @param Property $propertyNode
     */
    public function refactor(Node $propertyNode): ?Node
    {
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
