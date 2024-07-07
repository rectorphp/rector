<?php

declare (strict_types=1);
namespace Rector\Php82\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionProperty;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php81\Enum\AttributeName;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\Visibility;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php82\Rector\Class_\ReadOnlyClassRector\ReadOnlyClassRectorTest
 */
final class ReadOnlyClassRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ClassAnalyzer $classAnalyzer, VisibilityManipulator $visibilityManipulator, PhpAttributeAnalyzer $phpAttributeAnalyzer, ReflectionProvider $reflectionProvider)
    {
        $this->classAnalyzer = $classAnalyzer;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Decorate read-only class with `readonly` attribute', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
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
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        $this->visibilityManipulator->makeReadonly($node);
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod instanceof ClassMethod) {
            foreach ($constructClassMethod->getParams() as $param) {
                $this->visibilityManipulator->removeReadonly($param);
                if ($param->attrGroups !== []) {
                    // invoke reprint with correct newline
                    $param->setAttribute(AttributeKey::ORIGINAL_NODE, null);
                }
            }
        }
        foreach ($node->getProperties() as $property) {
            $this->visibilityManipulator->removeReadonly($property);
            if ($property->attrGroups !== []) {
                // invoke reprint with correct newline
                $property->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
        }
        if ($node->attrGroups !== []) {
            // invoke reprint with correct readonly newline
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::READONLY_CLASS;
    }
    /**
     * @return ClassReflection[]
     */
    private function resolveParentClassReflections(Scope $scope) : array
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return [];
        }
        return $classReflection->getParents();
    }
    /**
     * @param Property[] $properties
     */
    private function hasNonTypedProperty(array $properties) : bool
    {
        foreach ($properties as $property) {
            // properties of readonly class must always have type
            if ($property->type === null) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkip(Class_ $class, Scope $scope) : bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        if ($this->shouldSkipClass($class)) {
            return \true;
        }
        $parents = $this->resolveParentClassReflections($scope);
        if (!$class->isFinal()) {
            return !$this->isExtendsReadonlyClass($parents);
        }
        foreach ($parents as $parent) {
            if (!$parent->isReadOnly()) {
                return \true;
            }
        }
        $properties = $class->getProperties();
        if ($this->hasWritableProperty($properties)) {
            return \true;
        }
        if ($this->hasNonTypedProperty($properties)) {
            return \true;
        }
        if ($this->shouldSkipConsumeTraitProperty($class)) {
            return \true;
        }
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            // no __construct means no property promotion, skip if class has no property defined
            return $properties === [];
        }
        $params = $constructClassMethod->getParams();
        if ($params === []) {
            // no params means no property promotion, skip if class has no property defined
            return $properties === [];
        }
        return $this->shouldSkipParams($params);
    }
    private function shouldSkipConsumeTraitProperty(Class_ $class) : bool
    {
        $traitUses = $class->getTraitUses();
        foreach ($traitUses as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitName = $trait->toString();
                // trait not autoloaded
                if (!$this->reflectionProvider->hasClass($traitName)) {
                    return \true;
                }
                $traitClassReflection = $this->reflectionProvider->getClass($traitName);
                $nativeReflection = $traitClassReflection->getNativeReflection();
                if ($this->hasReadonlyProperty($nativeReflection->getProperties())) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @param ReflectionProperty[] $properties
     */
    private function hasReadonlyProperty(array $properties) : bool
    {
        foreach ($properties as $property) {
            if (!$property->isReadOnly()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param ClassReflection[] $parents
     */
    private function isExtendsReadonlyClass(array $parents) : bool
    {
        foreach ($parents as $parent) {
            if ($parent->isReadOnly()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Property[] $properties
     */
    private function hasWritableProperty(array $properties) : bool
    {
        foreach ($properties as $property) {
            if (!$property->isReadonly()) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        // need to have test fixture once feature added to  nikic/PHP-Parser
        if ($this->visibilityManipulator->hasVisibility($class, Visibility::READONLY)) {
            return \true;
        }
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return \true;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($class, AttributeName::ALLOW_DYNAMIC_PROPERTIES)) {
            return \true;
        }
        return $class->extends instanceof FullyQualified && !$this->reflectionProvider->hasClass($class->extends->toString());
    }
    /**
     * @param Param[] $params
     */
    private function shouldSkipParams(array $params) : bool
    {
        foreach ($params as $param) {
            // has non-readonly property promotion
            if (!$this->visibilityManipulator->hasVisibility($param, Visibility::READONLY) && $param->flags !== 0) {
                return \true;
            }
            // type is missing, invalid syntax
            if ($param->type === null) {
                return \true;
            }
        }
        return \false;
    }
}
