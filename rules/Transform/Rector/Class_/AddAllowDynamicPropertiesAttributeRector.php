<?php

namespace Rector\Transform\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
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
final class AddAllowDynamicPropertiesAttributeRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const ATTRIBUTE = 'AllowDynamicProperties';
    /**
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    /**
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @var \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer, \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer $phpAttributeAnalyzer, \Rector\PhpAttribute\Printer\PhpAttributeGroupFactory $phpAttributeGroupFactory, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add the `AllowDynamicProperties` attribute to all classes', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeObject {
    public string $someProperty = 'hello world';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[AllowDynamicProperties]
class SomeObject {
    public string $someProperty = 'hello world';
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->isDescendantOfStdclass($node) || $this->hasNeededAttributeAlready($node) || $this->hasMagicSetMethod($node)) {
            return null;
        }
        return $this->addAllowDynamicPropertiesAttribute($node);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::DEPRECATE_DYNAMIC_PROPERTIES;
    }
    private function isDescendantOfStdclass(\PhpParser\Node\Stmt\Class_ $node) : bool
    {
        if (!$node->extends instanceof \PhpParser\Node\Name\FullyQualified) {
            return \false;
        }
        $ancestorClassNames = $this->familyRelationsAnalyzer->getClassLikeAncestorNames($node);
        return \in_array('stdClass', $ancestorClassNames);
    }
    private function hasNeededAttributeAlready(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        $nodeHasAttribute = $this->phpAttributeAnalyzer->hasPhpAttribute($class, self::ATTRIBUTE);
        if ($nodeHasAttribute) {
            return \true;
        }
        if (!$class->extends instanceof \PhpParser\Node\Name\FullyQualified) {
            return \false;
        }
        return $this->phpAttributeAnalyzer->hasInheritedPhpAttribute($class, self::ATTRIBUTE);
    }
    private function hasMagicSetMethod(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        $classReflection = $this->reflectionProvider->getClass($class->namespacedName);
        return $classReflection->hasMethod('__set');
    }
    private function addAllowDynamicPropertiesAttribute(\PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Stmt\Class_
    {
        $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(self::ATTRIBUTE);
        $class->attrGroups[] = $attributeGroup;
        return $class;
    }
}
