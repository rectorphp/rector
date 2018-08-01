<?php declare(strict_types=1);

namespace Rector\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;
use Rector\NodeModifier\VisibilityModifier;
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

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        // doesn't have a parent class
        if (! $node->hasAttribute(Attribute::PARENT_CLASS_NAME)) {
            return false;
        }

        $nodeParentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (! isset($this->methodToVisibilityByClass[$nodeParentClassName])) {
            return false;
        }

        $methodName = $node->name->toString();

        return isset($this->methodToVisibilityByClass[$nodeParentClassName][$methodName]);
    }

    /**
     * @param ClassMethod $classMethodNode
     */
    public function refactor(Node $classMethodNode): ?Node
    {
        $this->visibilityModifier->removeOriginalVisibilityFromFlags($classMethodNode);

        $newVisibility = $this->resolveNewVisibilityForNode($classMethodNode);

        $this->visibilityModifier->addVisibilityFlag($classMethodNode, $newVisibility);

        return $classMethodNode;
    }

    private function resolveNewVisibilityForNode(ClassMethod $classMethodNode): string
    {
        $methodName = $classMethodNode->name->toString();
        $nodeParentClassName = $classMethodNode->getAttribute(Attribute::PARENT_CLASS_NAME);

        return $this->methodToVisibilityByClass[$nodeParentClassName][$methodName];
    }
}
