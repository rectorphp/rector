<?php declare(strict_types=1);

namespace Rector\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ChangePropertyVisibilityRector extends AbstractRector
{
    /**
     * @var string[][] { class => [ property name => visibility ] }
     */
    private $propertyToVisibilityByClass = [];

    /**
     * @param string[][] $propertyToVisibilityByClass
     */
    public function __construct(array $propertyToVisibilityByClass)
    {
        $this->propertyToVisibilityByClass = $propertyToVisibilityByClass;
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
            $classNode = $node->getAttribute(Attribute::CLASS_NODE);
            if ($classNode === null) {
                continue;
            }

            if (! $this->isType($classNode, $type)) {
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
