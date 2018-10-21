<?php declare(strict_types=1);

namespace Rector\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeModifier\VisibilityModifier;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ChangeMethodVisibilityRector extends AbstractRector
{
    /**
     * @var string[] { class => [ method name => visibility ] }
     */
    private $methodToVisibilityByClass = [];

    /**
     * @var VisibilityModifier
     */
    private $visibilityModifier;

    /**
     * @param string[] $methodToVisibilityByClass
     */
    public function __construct(array $methodToVisibilityByClass, VisibilityModifier $visibilityModifier)
    {
        $this->methodToVisibilityByClass = $methodToVisibilityByClass;
        $this->visibilityModifier = $visibilityModifier;
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
                    '$methodToVisibilityByClass' => [
                        'FrameworkClass' => [
                            'someMethod' => 'protected',
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
        if (! isset($this->methodToVisibilityByClass[$nodeParentClassName][$methodName])) {
            return null;
        }
        $this->visibilityModifier->removeOriginalVisibilityFromFlags($node);

        $newVisibility = $this->resolveNewVisibilityForNode($node);

        $this->visibilityModifier->addVisibilityFlag($node, $newVisibility);

        return $node;
    }

    private function resolveNewVisibilityForNode(ClassMethod $classMethodNode): string
    {
        $methodName = $this->getName($classMethodNode);
        $nodeParentClassName = $classMethodNode->getAttribute(Attribute::PARENT_CLASS_NAME);

        return $this->methodToVisibilityByClass[$nodeParentClassName][$methodName];
    }
}
