<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Enum\ObjectReference;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\Enum\PHPUnitAttribute;
use Rector\PHPUnit\Enum\PHPUnitClassName;
/**
 * PHPUnit counts assertions made in lifecycle methods of the class and its parents toward every test of the class
 */
final class LifecycleMethodAssertAnalyzer
{
    /**
     * @readonly
     */
    private \Rector\PHPUnit\NodeAnalyzer\AssertCallAnalyzer $assertCallAnalyzer;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @var string[]
     */
    private const HOOK_METHOD_NAMES = ['setUp', 'tearDown', 'assertPreConditions', 'assertPostConditions'];
    /**
     * @var string[]
     */
    private const HOOK_ATTRIBUTES = [PHPUnitAttribute::BEFORE, PHPUnitAttribute::AFTER, PHPUnitAttribute::PRE_CONDITION, PHPUnitAttribute::POST_CONDITION];
    /**
     * @var string[]
     */
    private const HOOK_ANNOTATIONS = ['before', 'after', 'preCondition', 'postCondition'];
    /**
     * Every parent class is parsed with full scope, so stop walking deep chains for performance
     * @var int
     */
    private const MAX_PARENT_CLASS_LEVEL = 5;
    /**
     * Assert, expectation and mock calls detected by AssertCallAnalyzer, matched in raw file contents
     * @var string
     */
    private const ASSERT_LIKE_TEXT_REGEX = '#assert|expect|should|mockbuilder|prophe#i';
    /**
     * @var array<string, bool>
     */
    private array $assertingByParentClassAndOverriddenHooks = [];
    /**
     * @var array<string, bool>
     */
    private array $hasAssertLikeTextByClassName = [];
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\AssertCallAnalyzer $assertCallAnalyzer, PhpAttributeAnalyzer $phpAttributeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver, AstResolver $astResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->assertCallAnalyzer = $assertCallAnalyzer;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function hasAssertingLifecycleMethod(Class_ $class, Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->isClassLikeAsserting($class, $classReflection, [], 0);
    }
    /**
     * @param string[] $overriddenHookMethodNames
     */
    private function isClassLikeAsserting(?ClassLike $classLike, ClassReflection $classReflection, array $overriddenHookMethodNames, int $parentClassLevel): bool
    {
        $classMethods = $classLike instanceof ClassLike ? $classLike->getMethods() : [];
        foreach ($classMethods as $classMethod) {
            $hookMethodName = $this->resolveHookMethodName($classMethod);
            if ($this->isRelevantHook($classMethod, $hookMethodName, $overriddenHookMethodNames) && $this->containsAssertCall($classMethod)) {
                return \true;
            }
            // a hook overridden here hides the parent one, unless it calls it through "parent::"
            if ($hookMethodName !== null && !$this->hasParentCall($classMethod, $hookMethodName)) {
                $overriddenHookMethodNames[] = $hookMethodName;
            }
        }
        if ($parentClassLevel >= self::MAX_PARENT_CLASS_LEVEL) {
            return \false;
        }
        $parentClassReflection = $classReflection->getParentClass();
        if (!$parentClassReflection instanceof ClassReflection) {
            return \false;
        }
        // parsing parents with full scope is expensive, skip chains without any assert-like text first
        if (!$this->hasAssertLikeTextInClassChain($parentClassReflection)) {
            return \false;
        }
        return $this->isParentClassAsserting($parentClassReflection, $overriddenHookMethodNames, $parentClassLevel + 1);
    }
    /**
     * @param string[] $overriddenHookMethodNames
     */
    private function isParentClassAsserting(ClassReflection $classReflection, array $overriddenHookMethodNames, int $parentClassLevel): bool
    {
        // do not analyze PHPUnit and Symfony base test cases, their hooks do not count as test assertions
        if (in_array($classReflection->getName(), PHPUnitClassName::FRAMEWORK_TEST_CASES, \true)) {
            return \false;
        }
        // the result of a parent only depends on the hooks overridden below it and its level, so test classes share the parent chain
        $overriddenHookMethodNames = array_unique($overriddenHookMethodNames);
        sort($overriddenHookMethodNames);
        $cacheKey = $classReflection->getName() . '|' . implode(',', $overriddenHookMethodNames) . '|' . $parentClassLevel;
        return $this->assertingByParentClassAndOverriddenHooks[$cacheKey] ??= $this->isClassLikeAsserting($this->astResolver->resolveClassFromClassReflection($classReflection), $classReflection, $overriddenHookMethodNames, $parentClassLevel);
    }
    private function hasAssertLikeTextInClassChain(ClassReflection $classReflection): bool
    {
        $className = $classReflection->getName();
        if (in_array($className, PHPUnitClassName::FRAMEWORK_TEST_CASES, \true)) {
            return \false;
        }
        if (isset($this->hasAssertLikeTextByClassName[$className])) {
            return $this->hasAssertLikeTextByClassName[$className];
        }
        // hooks can call helpers from used traits or from any parent, so check the whole chain
        $classReflections = array_merge([$classReflection], array_values($classReflection->getTraits(\true)));
        $hasAssertLikeText = \false;
        foreach ($classReflections as $currentClassReflection) {
            if ($this->hasAssertLikeText($currentClassReflection->getFileName())) {
                $hasAssertLikeText = \true;
                break;
            }
        }
        if (!$hasAssertLikeText) {
            $parentClassReflection = $classReflection->getParentClass();
            $hasAssertLikeText = $parentClassReflection instanceof ClassReflection && $this->hasAssertLikeTextInClassChain($parentClassReflection);
        }
        return $this->hasAssertLikeTextByClassName[$className] = $hasAssertLikeText;
    }
    private function hasAssertLikeText(?string $fileName): bool
    {
        // internal classes have no file to parse either
        if ($fileName === null) {
            return \false;
        }
        return preg_match(self::ASSERT_LIKE_TEXT_REGEX, (string) file_get_contents($fileName)) === 1;
    }
    private function containsAssertCall(ClassMethod $classMethod): bool
    {
        // every hook gets its own nesting budget, a "parent::" hook call is evaluated again in the parent itself
        $this->assertCallAnalyzer->resetNesting();
        return $this->assertCallAnalyzer->containsAssertCall($classMethod);
    }
    private function hasParentCall(ClassMethod $classMethod, string $hookMethodName): bool
    {
        return $this->betterNodeFinder->findFirst((array) $classMethod->stmts, fn(Node $node): bool => $node instanceof StaticCall && $this->nodeNameResolver->isName($node->class, ObjectReference::PARENT) && $this->nodeNameResolver->isName($node->name, $hookMethodName)) instanceof StaticCall;
    }
    private function resolveHookMethodName(ClassMethod $classMethod): ?string
    {
        $methodName = $classMethod->name->toString();
        foreach (self::HOOK_METHOD_NAMES as $hookMethodName) {
            if (strcasecmp($hookMethodName, $methodName) === 0) {
                return $hookMethodName;
            }
        }
        return null;
    }
    /**
     * @param string[] $overriddenHookMethodNames
     */
    private function isRelevantHook(ClassMethod $classMethod, ?string $hookMethodName, array $overriddenHookMethodNames): bool
    {
        if ($hookMethodName !== null && !in_array($hookMethodName, $overriddenHookMethodNames, \true)) {
            return \true;
        }
        // methods marked as hooks run too, also when overridden
        if ($classMethod->attrGroups !== [] && $this->phpAttributeAnalyzer->hasPhpAttributes($classMethod, self::HOOK_ATTRIBUTES)) {
            return \true;
        }
        if (!$classMethod->getDocComment() instanceof Doc) {
            return \false;
        }
        return $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod)->hasByNames(self::HOOK_ANNOTATIONS);
    }
}
