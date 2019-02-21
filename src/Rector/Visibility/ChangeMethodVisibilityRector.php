<?php declare(strict_types=1);

namespace Rector\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ChangeMethodVisibilityRector extends AbstractRector
{
    /**
     * @var string[][] { class => [ method name => visibility ] }
     */
    private $methodToVisibilityByClass = [];

    /**
     * @param string[][] $methodToVisibilityByClass
     */
    public function __construct(array $methodToVisibilityByClass)
    {
        $this->methodToVisibilityByClass = $methodToVisibilityByClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change visibility of method from parent class.',
            [new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
class FrameworkClass
{
    protected someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    public someMethod()
    {
    }
}
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
class FrameworkClass
{
    protected someMethod()
    {
    }
}

class MyClass extends FrameworkClass
{
    protected someMethod()
    {
    }
}
CODE_SAMPLE
                ,
                [
                    'FrameworkClass' => [
                        'someMethod' => 'protected',
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        // doesn't have a parent class
        if (! $node->hasAttribute(Attribute::PARENT_CLASS_NAME)) {
            return null;
        }

        $nodeParentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (! isset($this->methodToVisibilityByClass[$nodeParentClassName])) {
            return null;
        }
        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }

        if (! isset($this->methodToVisibilityByClass[$nodeParentClassName][$methodName])) {
            return null;
        }

        $nodeParentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        $visibility = $this->methodToVisibilityByClass[$nodeParentClassName][$methodName];

        $this->changeNodeVisibility($node, $visibility);

        return $node;
    }
}
