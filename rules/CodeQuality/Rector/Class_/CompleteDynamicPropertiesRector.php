<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\CodeQuality\NodeAnalyzer\LocalPropertyAnalyzer;
use Rector\CodeQuality\NodeAnalyzer\MissingPropertiesResolver;
use Rector\CodeQuality\NodeFactory\MissingPropertiesFactory;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ClassReflectionAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector\CompleteDynamicPropertiesRectorTest
 */
final class CompleteDynamicPropertiesRector extends AbstractRector
{
    /**
     * @readonly
     */
    private MissingPropertiesFactory $missingPropertiesFactory;
    /**
     * @readonly
     */
    private LocalPropertyAnalyzer $localPropertyAnalyzer;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ClassAnalyzer $classAnalyzer;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private MissingPropertiesResolver $missingPropertiesResolver;
    /**
     * @readonly
     */
    private ClassReflectionAnalyzer $classReflectionAnalyzer;
    public function __construct(MissingPropertiesFactory $missingPropertiesFactory, LocalPropertyAnalyzer $localPropertyAnalyzer, ReflectionProvider $reflectionProvider, ClassAnalyzer $classAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer, MissingPropertiesResolver $missingPropertiesResolver, ClassReflectionAnalyzer $classReflectionAnalyzer)
    {
        $this->missingPropertiesFactory = $missingPropertiesFactory;
        $this->localPropertyAnalyzer = $localPropertyAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
        $this->classAnalyzer = $classAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->missingPropertiesResolver = $missingPropertiesResolver;
        $this->classReflectionAnalyzer = $classReflectionAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add missing dynamic properties', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function set()
    {
        $this->value = 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int
     */
    public $value;

    public function set()
    {
        $this->value = 5;
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
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $classReflection = $this->matchClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        // special case for Laravel Collection macro magic
        $definedLocalPropertiesWithTypes = $this->localPropertyAnalyzer->resolveFetchedPropertiesToTypesFromClass($node);
        $propertiesToComplete = $this->missingPropertiesResolver->resolve($node, $classReflection, $definedLocalPropertiesWithTypes);
        $newProperties = $this->missingPropertiesFactory->create($propertiesToComplete);
        if ($newProperties === []) {
            return null;
        }
        $node->stmts = array_merge($newProperties, $node->stmts);
        return $node;
    }
    private function shouldSkipClass(Class_ $class): bool
    {
        if ($this->classAnalyzer->isAnonymousClass($class)) {
            return \true;
        }
        // abstract class property might be accessed from child class
        if ($class->isAbstract()) {
            return \true;
        }
        $className = (string) $this->getName($class);
        if (!$this->reflectionProvider->hasClass($className)) {
            return \true;
        }
        // dynamic property on purpose
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($class, 'AllowDynamicProperties')) {
            return \true;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        // properties are accessed via magic, nothing we can do
        if ($classReflection->hasMethod('__set')) {
            return \true;
        }
        if ($classReflection->hasMethod('__get')) {
            return \true;
        }
        // any not autoloaded ancestor may already declare the property, so we cannot safely add it
        return $this->hasNotAutoloadedAncestor($classReflection);
    }
    private function hasNotAutoloadedAncestor(ClassReflection $classReflection): bool
    {
        $currentClassReflection = $classReflection;
        while ($currentClassReflection instanceof ClassReflection) {
            $parentClassName = $this->classReflectionAnalyzer->resolveParentClassName($currentClassReflection);
            if ($parentClassName !== null && !$this->reflectionProvider->hasClass($parentClassName)) {
                return \true;
            }
            $currentClassReflection = $currentClassReflection->getParentClass();
        }
        return \false;
    }
    private function matchClassReflection(Class_ $class): ?ClassReflection
    {
        $className = $this->getName($class);
        if ($className === null) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        return $this->reflectionProvider->getClass($className);
    }
}
