<?php

declare (strict_types=1);
namespace Rector\NodeCollector\NodeCollector;

use RectorPrefix20210514\Nette\Utils\Arrays;
use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodReferenceAnalyzer;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use ReflectionMethod;
/**
 * This service contains all the parsed nodes. E.g. all the functions, method call, classes, static calls etc. It's
 * useful in case of context analysis, e.g. find all the usage of class method to detect, if the method is used.
 */
final class NodeRepository
{
    /**
     * @var array<class-string, ClassMethod[]>
     */
    private $classMethodsByType = [];
    /**
     * @var array<string, Function_>
     */
    private $functionsByName = [];
    /**
     * @var array<class-string, array<array<MethodCall|StaticCall>>>
     */
    private $callsByTypeAndMethod = [];
    /**
     * E.g. [$this, 'someLocalMethod']
     *
     * @var array<string, array<string, ArrayCallable[]>>
     */
    private $arrayCallablesByTypeAndMethod = [];
    /**
     * @var array<string, Attribute[]>
     */
    private $attributes = [];
    /**
     * @var \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodReferenceAnalyzer
     */
    private $arrayCallableMethodReferenceAnalyzer;
    /**
     * @var \Rector\NodeCollector\NodeCollector\ParsedPropertyFetchNodeCollector
     */
    private $parsedPropertyFetchNodeCollector;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeCollector\NodeCollector\ParsedNodeCollector
     */
    private $parsedNodeCollector;
    /**
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodReferenceAnalyzer $arrayCallableMethodReferenceAnalyzer, \Rector\NodeCollector\NodeCollector\ParsedPropertyFetchNodeCollector $parsedPropertyFetchNodeCollector, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeCollector\NodeCollector\ParsedNodeCollector $parsedNodeCollector, \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper $typeUnwrapper, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->arrayCallableMethodReferenceAnalyzer = $arrayCallableMethodReferenceAnalyzer;
        $this->parsedPropertyFetchNodeCollector = $parsedPropertyFetchNodeCollector;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function collect(\PhpParser\Node $node) : void
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $this->addMethod($node);
            return;
        }
        // array callable - [$this, 'someCall']
        if ($node instanceof \PhpParser\Node\Expr\Array_) {
            $this->collectArray($node);
            return;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\StaticCall) {
            $this->addCall($node);
        }
        if ($node instanceof \PhpParser\Node\Stmt\Function_) {
            $functionName = $this->nodeNameResolver->getName($node);
            $this->functionsByName[$functionName] = $node;
        }
        if ($node instanceof \PhpParser\Node\Attribute) {
            $attributeClass = $this->nodeNameResolver->getName($node->name);
            $this->attributes[$attributeClass][] = $node;
        }
    }
    public function findFunction(string $name) : ?\PhpParser\Node\Stmt\Function_
    {
        return $this->functionsByName[$name] ?? null;
    }
    /**
     * @return array<string, MethodCall[]|StaticCall[]>
     */
    public function findMethodCallsOnClass(string $className) : array
    {
        return $this->callsByTypeAndMethod[$className] ?? [];
    }
    /**
     * @return StaticCall[]
     */
    public function findStaticCallsByClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $calls = $this->findCallsByClassMethod($classMethod);
        return \array_filter($calls, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Expr\StaticCall;
        });
    }
    public function findClassMethodByStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $method = $this->nodeNameResolver->getName($staticCall->name);
        if ($method === null) {
            return null;
        }
        $objectType = $this->nodeTypeResolver->resolve($staticCall->class);
        $classes = \PHPStan\Type\TypeUtils::getDirectClassNames($objectType);
        foreach ($classes as $class) {
            $possibleClassMethod = $this->findClassMethod($class, $method);
            if ($possibleClassMethod !== null) {
                return $possibleClassMethod;
            }
        }
        return null;
    }
    public function findClassMethod(string $className, string $methodName) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (\RectorPrefix20210514\Nette\Utils\Strings::contains($methodName, '\\')) {
            $message = \sprintf('Class and method arguments are switched in "%s"', __METHOD__);
            throw new \Rector\Core\Exception\ShouldNotHappenException($message);
        }
        if (isset($this->classMethodsByType[$className][$methodName])) {
            return $this->classMethodsByType[$className][$methodName];
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (isset($this->classMethodsByType[$parentClassReflection->getName()][$methodName])) {
                return $this->classMethodsByType[$parentClassReflection->getName()][$methodName];
            }
        }
        return null;
    }
    /**
     * @return MethodCall[]
     */
    public function getMethodsCalls() : array
    {
        $calls = \RectorPrefix20210514\Nette\Utils\Arrays::flatten($this->callsByTypeAndMethod);
        return \array_filter($calls, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Expr\MethodCall;
        });
    }
    /**
     * @param MethodReflection|ReflectionMethod $methodReflection
     */
    public function findClassMethodByMethodReflection($methodReflection) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $methodName = $methodReflection->getName();
        $declaringClass = $methodReflection->getDeclaringClass();
        $className = $declaringClass->getName();
        return $this->findClassMethod($className, $methodName);
    }
    /**
     * @return PropertyFetch[]
     */
    public function findPropertyFetchesByProperty(\PhpParser\Node\Stmt\Property $property) : array
    {
        /** @var string|null $className */
        $className = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
            return [];
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        return $this->parsedPropertyFetchNodeCollector->findPropertyFetchesByTypeAndName($className, $propertyName);
    }
    /**
     * @return PropertyFetch[]
     */
    public function findPropertyFetchesByPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : array
    {
        $propertyFetcheeType = $this->nodeTypeResolver->getStaticType($propertyFetch->var);
        if (!$propertyFetcheeType instanceof \PHPStan\Type\TypeWithClassName) {
            return [];
        }
        $className = $this->nodeTypeResolver->getFullyQualifiedClassName($propertyFetcheeType);
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($propertyFetch);
        return $this->parsedPropertyFetchNodeCollector->findPropertyFetchesByTypeAndName($className, $propertyName);
    }
    /**
     * @return MethodCall[]|StaticCall[]|ArrayCallable[]
     */
    public function findCallsByClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $class = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if (!\is_string($class)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        return $this->findCallsByClassAndMethod($class, $methodName);
    }
    public function hasClassChildren(\PhpParser\Node\Stmt\Class_ $desiredClass) : bool
    {
        $desiredClassName = $desiredClass->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($desiredClassName === null) {
            return \false;
        }
        foreach ($this->parsedNodeCollector->getClasses() as $classNode) {
            $currentClassName = $classNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            if ($currentClassName === null) {
                continue;
            }
            if (!$this->isChildOrEqualClassLike($desiredClassName, $currentClassName)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
    /**
     * @return Class_[]
     */
    public function findClassesBySuffix(string $suffix) : array
    {
        $classNodes = [];
        foreach ($this->parsedNodeCollector->getClasses() as $className => $classNode) {
            if (!\RectorPrefix20210514\Nette\Utils\Strings::endsWith($className, $suffix)) {
                continue;
            }
            $classNodes[] = $classNode;
        }
        return $classNodes;
    }
    /**
     * @return Trait_[]
     */
    public function findUsedTraitsInClass(\PhpParser\Node\Stmt\ClassLike $classLike) : array
    {
        $traits = [];
        foreach ($classLike->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitName = $this->nodeNameResolver->getName($trait);
                $foundTrait = $this->parsedNodeCollector->findTrait($traitName);
                if ($foundTrait !== null) {
                    $traits[] = $foundTrait;
                }
            }
        }
        return $traits;
    }
    /**
     * @return Class_[]|Interface_[]
     */
    public function findClassesAndInterfacesByType(string $type) : array
    {
        return \array_merge($this->findChildrenOfClass($type), $this->findImplementersOfInterface($type));
    }
    /**
     * @return Class_[]
     */
    public function findChildrenOfClass(string $class) : array
    {
        $childrenClasses = [];
        foreach ($this->parsedNodeCollector->getClasses() as $classNode) {
            $currentClassName = $classNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            if (!$this->isChildOrEqualClassLike($class, $currentClassName)) {
                continue;
            }
            $childrenClasses[] = $classNode;
        }
        return $childrenClasses;
    }
    public function findInterface(string $class) : ?\PhpParser\Node\Stmt\Interface_
    {
        return $this->parsedNodeCollector->findInterface($class);
    }
    public function findClass(string $name) : ?\PhpParser\Node\Stmt\Class_
    {
        return $this->parsedNodeCollector->findClass($name);
    }
    public function findClassMethodByMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $className = $this->resolveCallerClassName($methodCall);
        if ($className === null) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }
        return $this->findClassMethod($className, $methodName);
    }
    public function findClassConstByClassConstFetch(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch) : ?\PhpParser\Node\Stmt\ClassConst
    {
        return $this->parsedNodeCollector->findClassConstByClassConstFetch($classConstFetch);
    }
    /**
     * @return Attribute[]
     */
    public function findAttributes(string $class) : array
    {
        return $this->attributes[$class] ?? [];
    }
    /**
     * @param PropertyFetch|StaticPropertyFetch $expr
     */
    public function findPropertyByPropertyFetch(\PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Stmt\Property
    {
        $propertyCaller = $expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch ? $expr->class : $expr->var;
        $propertyCallerType = $this->nodeTypeResolver->getStaticType($propertyCaller);
        if (!$propertyCallerType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $className = $this->nodeTypeResolver->getFullyQualifiedClassName($propertyCallerType);
        $class = $this->findClass($className);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($expr->name);
        if ($propertyName === null) {
            return null;
        }
        return $class->getProperty($propertyName);
    }
    /**
     * @return Class_[]
     */
    public function getClasses() : array
    {
        return $this->parsedNodeCollector->getClasses();
    }
    public function findClassConstant(string $className, string $constantName) : ?\PhpParser\Node\Stmt\ClassConst
    {
        return $this->parsedNodeCollector->findClassConstant($className, $constantName);
    }
    public function findTrait(string $name) : ?\PhpParser\Node\Stmt\Trait_
    {
        return $this->parsedNodeCollector->findTrait($name);
    }
    public function findByShortName(string $shortName) : ?\PhpParser\Node\Stmt\Class_
    {
        return $this->parsedNodeCollector->findByShortName($shortName);
    }
    /**
     * @return StaticCall[]
     */
    public function getStaticCalls() : array
    {
        return $this->parsedNodeCollector->getStaticCalls();
    }
    public function resolveCallerClassName(\PhpParser\Node\Expr\MethodCall $methodCall) : ?string
    {
        $callerType = $this->nodeTypeResolver->getStaticType($methodCall->var);
        $callerObjectType = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($callerType);
        if (!$callerObjectType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        return $callerObjectType->getClassName();
    }
    public function findClassLike(string $classLikeName) : ?\PhpParser\Node\Stmt\ClassLike
    {
        return $this->findClass($classLikeName) ?? $this->findInterface($classLikeName) ?? $this->findTrait($classLikeName);
    }
    private function collectArray(\PhpParser\Node\Expr\Array_ $array) : void
    {
        $arrayCallable = $this->arrayCallableMethodReferenceAnalyzer->match($array);
        if (!$arrayCallable instanceof \Rector\NodeCollector\ValueObject\ArrayCallable) {
            return;
        }
        if (!$this->reflectionProvider->hasClass($arrayCallable->getClass())) {
            return;
        }
        $classReflection = $this->reflectionProvider->getClass($arrayCallable->getClass());
        if (!$classReflection->isClass()) {
            return;
        }
        if (!$classReflection->hasMethod($arrayCallable->getMethod())) {
            return;
        }
        $this->arrayCallablesByTypeAndMethod[$arrayCallable->getClass()][$arrayCallable->getMethod()][] = $arrayCallable;
    }
    private function addMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $className = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        // anonymous
        if ($className === null) {
            return;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $this->classMethodsByType[$className][$methodName] = $classMethod;
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    private function addCall(\PhpParser\Node $node) : void
    {
        // one node can be of multiple-class types
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            $classType = $this->resolveNodeClassTypes($node->var);
        } else {
            /** @var StaticCall $node */
            $classType = $this->resolveNodeClassTypes($node->class);
        }
        // anonymous
        if ($classType instanceof \PHPStan\Type\MixedType) {
            return;
        }
        $methodName = $this->nodeNameResolver->getName($node->name);
        if ($methodName === null) {
            return;
        }
        $this->addCallByType($node, $classType, $methodName);
    }
    /**
     * @return MethodCall[]|StaticCall[]|ArrayCallable[]
     */
    private function findCallsByClassAndMethod(string $className, string $methodName) : array
    {
        return $this->callsByTypeAndMethod[$className][$methodName] ?? $this->arrayCallablesByTypeAndMethod[$className][$methodName] ?? [];
    }
    private function isChildOrEqualClassLike(string $desiredClass, ?string $currentClassName) : bool
    {
        if ($currentClassName === null) {
            return \false;
        }
        if (!$this->reflectionProvider->hasClass($desiredClass)) {
            return \false;
        }
        if (!$this->reflectionProvider->hasClass($currentClassName)) {
            return \false;
        }
        $desiredClassReflection = $this->reflectionProvider->getClass($desiredClass);
        $currentClassReflection = $this->reflectionProvider->getClass($currentClassName);
        if (!$currentClassReflection->isSubclassOf($desiredClassReflection->getName())) {
            return \false;
        }
        return $currentClassName !== $desiredClass;
    }
    /**
     * @return Interface_[]
     */
    private function findImplementersOfInterface(string $interface) : array
    {
        $implementerInterfaces = [];
        foreach ($this->parsedNodeCollector->getInterfaces() as $interfaceNode) {
            $className = $interfaceNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            if (!$this->isChildOrEqualClassLike($interface, $className)) {
                continue;
            }
            $implementerInterfaces[] = $interfaceNode;
        }
        return $implementerInterfaces;
    }
    private function resolveNodeClassTypes(\PhpParser\Node $node) : \PHPStan\Type\Type
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall && $node->var instanceof \PhpParser\Node\Expr\Variable && $node->var->name === 'this') {
            /** @var string|null $className */
            $className = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
            if ($className) {
                return new \PHPStan\Type\ObjectType($className);
            }
            return new \PHPStan\Type\MixedType();
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->nodeTypeResolver->resolve($node->var);
        }
        return $this->nodeTypeResolver->resolve($node);
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    private function addCallByType(\PhpParser\Node $node, \PHPStan\Type\Type $classType, string $methodName) : void
    {
        if ($classType instanceof \PHPStan\Type\TypeWithClassName) {
            if ($classType instanceof \PHPStan\Type\ThisType) {
                $classType = $classType->getStaticObjectType();
            }
            $this->callsByTypeAndMethod[$classType->getClassName()][$methodName][] = $node;
            $this->addParentTypeWithClassName($classType, $node, $methodName);
        }
        if ($classType instanceof \PHPStan\Type\UnionType) {
            foreach ($classType->getTypes() as $unionedType) {
                if (!$unionedType instanceof \PHPStan\Type\ObjectType) {
                    continue;
                }
                $this->callsByTypeAndMethod[$unionedType->getClassName()][$methodName][] = $node;
            }
        }
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    private function addParentTypeWithClassName(\PHPStan\Type\TypeWithClassName $typeWithClassName, \PhpParser\Node $node, string $methodName) : void
    {
        // include also parent types
        if (!$typeWithClassName instanceof \PHPStan\Type\ObjectType) {
            return;
        }
        if (!$this->reflectionProvider->hasClass($typeWithClassName->getClassName())) {
            return;
        }
        $classReflection = $this->reflectionProvider->getClass($typeWithClassName->getClassName());
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            $this->callsByTypeAndMethod[$ancestorClassReflection->getName()][$methodName][] = $node;
        }
    }
}
