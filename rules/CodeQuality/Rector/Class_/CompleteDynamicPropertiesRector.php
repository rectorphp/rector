<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\CodeQuality\NodeAnalyzer\LocalPropertyAnalyzer;
use Rector\CodeQuality\NodeAnalyzer\MissingPropertiesResolver;
use Rector\CodeQuality\NodeFactory\MissingPropertiesFactory;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Rector\AbstractRector;
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
    public function __construct(MissingPropertiesFactory $missingPropertiesFactory, LocalPropertyAnalyzer $localPropertyAnalyzer, ReflectionProvider $reflectionProvider, ClassAnalyzer $classAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer, MissingPropertiesResolver $missingPropertiesResolver)
    {
        $this->missingPropertiesFactory = $missingPropertiesFactory;
        $this->localPropertyAnalyzer = $localPropertyAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
        $this->classAnalyzer = $classAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->missingPropertiesResolver = $missingPropertiesResolver;
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
        return $class->extends instanceof FullyQualified && !$this->reflectionProvider->hasClass($class->extends->toString());
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
