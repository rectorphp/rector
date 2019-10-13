<?php

declare(strict_types=1);

namespace Rector\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Tests\Rector\Visibility\ChangePropertyVisibilityRector\ChangePropertyVisibilityRectorTest
 */
final class ChangePropertyVisibilityRector extends AbstractRector
{
    /**
     * @var string[][] { class => [ property name => visibility ] }
     */
    private $propertyToVisibilityByClass = [];

    /**
     * @param string[][] $propertyToVisibilityByClass
     */
    public function __construct(array $propertyToVisibilityByClass = [])
    {
        $this->propertyToVisibilityByClass = $propertyToVisibilityByClass;
    }

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
                    'FrameworkClass' => [
                        'someProperty' => 'protected',
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
}
