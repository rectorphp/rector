<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/deprecate_dynamic_properties
 *
 * @see \Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\AddAllowDynamicPropertiesAttributeRectorTest
 */
final class AddAllowDynamicPropertiesAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const ATTRIBUTE = 'AllowDynamicProperties';

    public function __construct(
        private readonly FamilyRelationsAnalyzer $familyRelationsAnalyzer,
        private readonly PhpAttributeAnalyzer $phpAttributeAnalyzer,
        private readonly PhpAttributeGroupFactory $phpAttributeGroupFactory,
        private readonly ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add the `AllowDynamicProperties` attribute to all classes', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeObject {
    public string $someProperty = 'hello world';
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
#[AllowDynamicProperties]
class SomeObject {
    public string $someProperty = 'hello world';
}
CODE_SAMPLE
            ),
        ]);
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        return $this->addAllowDynamicPropertiesAttribute($node);
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_DYNAMIC_PROPERTIES;
    }

    private function isDescendantOfStdclass(Class_ $class): bool
    {
        if (! $class->extends instanceof FullyQualified) {
            return false;
        }

        $ancestorClassNames = $this->familyRelationsAnalyzer->getClassLikeAncestorNames($class);
        return in_array('stdClass', $ancestorClassNames, true);
    }

    private function hasNeededAttributeAlready(Class_ $class): bool
    {
        $nodeHasAttribute = $this->phpAttributeAnalyzer->hasPhpAttribute($class, self::ATTRIBUTE);
        if ($nodeHasAttribute) {
            return true;
        }

        if (! $class->extends instanceof FullyQualified) {
            return false;
        }

        return $this->phpAttributeAnalyzer->hasInheritedPhpAttribute($class, self::ATTRIBUTE);
    }

    private function addAllowDynamicPropertiesAttribute(Class_ $class): Class_
    {
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(self::ATTRIBUTE);
        $class->attrGroups[] = $attributeGroup;

        return $class;
    }

    private function shouldSkip(Class_ $class): bool
    {
        if ($this->isDescendantOfStdclass($class)) {
            return true;
        }

        if ($this->hasNeededAttributeAlready($class)) {
            return true;
        }

        return $this->hasMagicSetMethod($class);
    }

    private function hasMagicSetMethod(Class_ $class): bool
    {
        $className = (string) $this->getName($class);
        $classReflection = $this->reflectionProvider->getClass($className);
        return $classReflection->hasMethod(MethodName::__SET);
    }
}
