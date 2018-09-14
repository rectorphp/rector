<?php declare(strict_types=1);

namespace Rector\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeModifier\VisibilityModifier;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Tests\Rector\Visibility\ChangeConstantVisibilityRector\Source\ParentObject;

final class ChangeConstantVisibilityRector extends AbstractRector
{
    /**
     * @var string[][] { class => [ method name => visibility ] }
     */
    private $constantToVisibilityByClass = [];

    /**
     * @var VisibilityModifier
     */
    private $visibilityModifier;

    /**
     * @param string[][] $constantToVisibilityByClass
     */
    public function __construct(array $constantToVisibilityByClass, VisibilityModifier $visibilityModifier)
    {
        $this->constantToVisibilityByClass = $constantToVisibilityByClass;
        $this->visibilityModifier = $visibilityModifier;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change visibility of constant from parent class.',
            [new ConfiguredCodeSample(
<<<'CODE_SAMPLE'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    public const SOME_CONSTANT = 1;
}
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}
CODE_SAMPLE
                ,
                [
                    ParentObject::class => [
                        '$constantToVisibilityByClass' => [
                            'SOME_CONSTANT' => 'protected',
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
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $classConstantNode
     */
    public function refactor(Node $classConstantNode): ?Node
    {
        // doesn't have a parent class
        if (! $classConstantNode->hasAttribute(Attribute::PARENT_CLASS_NAME)) {
            return null;
        }
        $nodeParentClassName = $classConstantNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (! isset($this->constantToVisibilityByClass[$nodeParentClassName])) {
            return null;
        }
        $constantName = $classConstantNode->consts[0]->name->toString();
        if (isset($this->constantToVisibilityByClass[$nodeParentClassName][$constantName]) === false) {
            return null;
        }
        $this->visibilityModifier->removeOriginalVisibilityFromFlags($classConstantNode);

        $newVisibility = $this->resolveNewVisibilityForNode($classConstantNode);
        $this->visibilityModifier->addVisibilityFlag($classConstantNode, $newVisibility);

        return $classConstantNode;
    }

    private function resolveNewVisibilityForNode(ClassConst $classConstantNode): string
    {
        $nodeParentClassName = $classConstantNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        $constantName = $classConstantNode->consts[0]->name->toString();

        return $this->constantToVisibilityByClass[$nodeParentClassName][$constantName];
    }
}
