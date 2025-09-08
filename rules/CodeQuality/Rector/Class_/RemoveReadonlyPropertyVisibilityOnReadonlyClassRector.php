<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Php81\NodeManipulator\AttributeGroupNewLiner;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Class_\RemoveReadonlyPropertyVisibilityOnReadonlyClassRector\RemoveReadonlyPropertyVisibilityOnReadonlyClassRectorTest
 */
final class RemoveReadonlyPropertyVisibilityOnReadonlyClassRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private VisibilityManipulator $visibilityManipulator;
    /**
     * @readonly
     */
    private AttributeGroupNewLiner $attributeGroupNewLiner;
    public function __construct(VisibilityManipulator $visibilityManipulator, AttributeGroupNewLiner $attributeGroupNewLiner)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->attributeGroupNewLiner = $attributeGroupNewLiner;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::READONLY_CLASS;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove readonly property visibility on readonly class', [new CodeSample(<<<'CODE_SAMPLE'
final readonly class SomeClass
{
    public function __construct(
        private readonly string $name
    ) {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final readonly class SomeClass
{
    public function __construct(
        private string $name
    ) {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->isReadonly()) {
            return null;
        }
        $hasChanged = \false;
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod instanceof ClassMethod) {
            foreach ($constructClassMethod->getParams() as $param) {
                if (!$param->isReadonly()) {
                    continue;
                }
                $this->visibilityManipulator->removeReadonly($param);
                $hasChanged = \true;
                if ($param->attrGroups !== []) {
                    $this->attributeGroupNewLiner->newLine($this->file, $param);
                }
            }
        }
        foreach ($node->getProperties() as $property) {
            if (!$property->isReadonly()) {
                continue;
            }
            $this->visibilityManipulator->removeReadonly($property);
            $hasChanged = \true;
            if ($property->attrGroups !== []) {
                $this->attributeGroupNewLiner->newLine($this->file, $property);
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
