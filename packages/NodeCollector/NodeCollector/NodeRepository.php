<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

use Nette\Utils\Arrays;
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
    private array $classMethodsByType = [];

    /**
     * @var array<string, Function_>
     */
    private array $functionsByName = [];

    /**
     * @var array<class-string, array<array<MethodCall|StaticCall>>>
     */
    private array $callsByTypeAndMethod = [];

    /**
     * E.g. [$this, 'someLocalMethod']
     *
     * @var array<string, array<string, ArrayCallable[]>>
     */
    private array $arrayCallablesByTypeAndMethod = [];

    /**
     * @var array<string, Attribute[]>
     */
    private array $attributes = [];

    public function __construct(
        private ArrayCallableMethodReferenceAnalyzer $arrayCallableMethodReferenceAnalyzer,
        private ParsedPropertyFetchNodeCollector $parsedPropertyFetchNodeCollector,
        private NodeNameResolver $nodeNameResolver,
        private ParsedNodeCollector $parsedNodeCollector,
        private TypeUnwrapper $typeUnwrapper,
        private ReflectionProvider $reflectionProvider,
        private NodeTypeResolver $nodeTypeResolver
    ) {
    }

    public function collect(Node $node): void
    {
        if ($node instanceof ClassMethod) {
            $this->addMethod($node);
            return;
        }

        // array callable - [$this, 'someCall']
        if ($node instanceof Array_) {
            $this->collectArray($node);
            return;
        }

        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $this->addCall($node);
        }

        if ($node instanceof Function_) {
            $functionName = $this->nodeNameResolver->getName($node);
            $this->functionsByName[$functionName] = $node;
        }

        if ($node instanceof Attribute) {
            $attributeClass = $this->nodeNameResolver->getName($node->name);
            $this->attributes[$attributeClass][] = $node;
        }
    }

    public function findFunction(string $name): ?Function_
    {
        return $this->functionsByName[$name] ?? null;
    }

    /**
     * @return array<string, MethodCall[]|StaticCall[]>
     */
    public function findMethodCallsOnClass(string $className): array
    {
        return $this->callsByTypeAndMethod[$className] ?? [];
    }

    /**
     * @return StaticCall[]
     */
    public function findStaticCallsByClassMethod(ClassMethod $classMethod): array
    {
        $calls = $this->findCallsByClassMethod($classMethod);
        return array_filter($calls, fn (Node $node): bool => $node instanceof StaticCall);
    }

    public function findClassMethodByStaticCall(StaticCall $staticCall): ?ClassMethod
    {
        $method = $this->nodeNameResolver->getName($staticCall->name);
        if ($method === null) {
            return null;
        }

        $objectType = $this->nodeTypeResolver->resolve($staticCall->class);
        $classes = TypeUtils::getDirectClassNames($objectType);

        foreach ($classes as $class) {
            $possibleClassMethod = $this->findClassMethod($class, $method);
            if ($possibleClassMethod !== null) {
                return $possibleClassMethod;
            }
        }

        return null;
    }

    public function findClassMethod(string $className, string $methodName): ?ClassMethod
    {
        if (\str_contains($methodName, '\\')) {
            $message = sprintf('Class and method arguments are switched in "%s"', __METHOD__);
            throw new ShouldNotHappenException($message);
        }

        if (isset($this->classMethodsByType[$className][$methodName])) {
            return $this->classMethodsByType[$className][$methodName];
        }

        if (! $this->reflectionProvider->hasClass($className)) {
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
    public function getMethodsCalls(): array
    {
        $calls = Arrays::flatten($this->callsByTypeAndMethod);

        return array_filter($calls, fn (Node $node): bool => $node instanceof MethodCall);
    }

    /**
     * @param MethodReflection|ReflectionMethod $methodReflection
     */
    public function findClassMethodByMethodReflection(object $methodReflection): ?ClassMethod
    {
        $methodName = $methodReflection->getName();

        $declaringClass = $methodReflection->getDeclaringClass();
        $className = $declaringClass->getName();

        return $this->findClassMethod($className, $methodName);
    }

    /**
     * @return PropertyFetch[]
     */
    public function findPropertyFetchesByProperty(Property $property): array
    {
        /** @var string|null $className */
        $className = $property->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return [];
        }

        $propertyName = $this->nodeNameResolver->getName($property);
        return $this->parsedPropertyFetchNodeCollector->findPropertyFetchesByTypeAndName($className, $propertyName);
    }

    /**
     * @return PropertyFetch[]
     */
    public function findPropertyFetchesByPropertyFetch(PropertyFetch $propertyFetch): array
    {
        $propertyFetcheeType = $this->nodeTypeResolver->getStaticType($propertyFetch->var);
        if (! $propertyFetcheeType instanceof TypeWithClassName) {
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
    public function findCallsByClassMethod(ClassMethod $classMethod): array
    {
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($class)) {
            throw new ShouldNotHappenException();
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);
        return $this->findCallsByClassAndMethod($class, $methodName);
    }

    public function hasClassChildren(Class_ $desiredClass): bool
    {
        $desiredClassName = $desiredClass->getAttribute(AttributeKey::CLASS_NAME);
        if ($desiredClassName === null) {
            return false;
        }

        foreach ($this->parsedNodeCollector->getClasses() as $classNode) {
            $currentClassName = $classNode->getAttribute(AttributeKey::CLASS_NAME);
            if ($currentClassName === null) {
                continue;
            }

            if (! $this->isChildOrEqualClassLike($desiredClassName, $currentClassName)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return Class_[]
     */
    public function findClassesBySuffix(string $suffix): array
    {
        $classNodes = [];

        foreach ($this->parsedNodeCollector->getClasses() as $className => $classNode) {
            if (! \str_ends_with($className, $suffix)) {
                continue;
            }

            $classNodes[] = $classNode;
        }

        return $classNodes;
    }

    /**
     * @return Trait_[]
     */
    public function findUsedTraitsInClass(ClassLike $classLike): array
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
    public function findClassesAndInterfacesByType(string $type): array
    {
        return array_merge($this->findChildrenOfClass($type), $this->findImplementersOfInterface($type));
    }

    /**
     * @return Class_[]
     */
    public function findChildrenOfClass(string $class): array
    {
        $childrenClasses = [];

        foreach ($this->parsedNodeCollector->getClasses() as $classNode) {
            $currentClassName = $classNode->getAttribute(AttributeKey::CLASS_NAME);
            if (! $this->isChildOrEqualClassLike($class, $currentClassName)) {
                continue;
            }

            $childrenClasses[] = $classNode;
        }

        return $childrenClasses;
    }

    public function findInterface(string $class): ?Interface_
    {
        return $this->parsedNodeCollector->findInterface($class);
    }

    public function findClass(string $name): ?Class_
    {
        return $this->parsedNodeCollector->findClass($name);
    }

    public function findClassMethodByMethodCall(MethodCall $methodCall): ?ClassMethod
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

    public function findClassConstByClassConstFetch(ClassConstFetch $classConstFetch): ?ClassConst
    {
        return $this->parsedNodeCollector->findClassConstByClassConstFetch($classConstFetch);
    }

    /**
     * @return Attribute[]
     */
    public function findAttributes(string $class): array
    {
        return $this->attributes[$class] ?? [];
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $expr
     */
    public function findPropertyByPropertyFetch(Expr $expr): ?Property
    {
        $propertyCaller = $expr instanceof StaticPropertyFetch ? $expr->class : $expr->var;

        $propertyCallerType = $this->nodeTypeResolver->getStaticType($propertyCaller);
        if (! $propertyCallerType instanceof TypeWithClassName) {
            return null;
        }

        $className = $this->nodeTypeResolver->getFullyQualifiedClassName($propertyCallerType);
        $class = $this->findClass($className);
        if (! $class instanceof Class_) {
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
    public function getClasses(): array
    {
        return $this->parsedNodeCollector->getClasses();
    }

    public function findClassConstant(string $className, string $constantName): ?ClassConst
    {
        return $this->parsedNodeCollector->findClassConstant($className, $constantName);
    }

    public function findTrait(string $name): ?Trait_
    {
        return $this->parsedNodeCollector->findTrait($name);
    }

    public function findByShortName(string $shortName): ?Class_
    {
        return $this->parsedNodeCollector->findByShortName($shortName);
    }

    /**
     * @return StaticCall[]
     */
    public function getStaticCalls(): array
    {
        return $this->parsedNodeCollector->getStaticCalls();
    }

    public function resolveCallerClassName(MethodCall $methodCall): ?string
    {
        $callerType = $this->nodeTypeResolver->getStaticType($methodCall->var);
        $callerObjectType = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($callerType);
        if (! $callerObjectType instanceof TypeWithClassName) {
            return null;
        }

        return $callerObjectType->getClassName();
    }

    public function findClassLike(string $classLikeName): ?ClassLike
    {
        return $this->findClass($classLikeName) ?? $this->findInterface($classLikeName) ?? $this->findTrait(
            $classLikeName
        );
    }

    private function collectArray(Array_ $array): void
    {
        $arrayCallable = $this->arrayCallableMethodReferenceAnalyzer->match($array);
        if (! $arrayCallable instanceof ArrayCallable) {
            return;
        }

        if (! $this->reflectionProvider->hasClass($arrayCallable->getClass())) {
            return;
        }

        $classReflection = $this->reflectionProvider->getClass($arrayCallable->getClass());
        if (! $classReflection->isClass()) {
            return;
        }

        if (! $classReflection->hasMethod($arrayCallable->getMethod())) {
            return;
        }

        $this->arrayCallablesByTypeAndMethod[$arrayCallable->getClass()][strtolower(
            $arrayCallable->getMethod()
        )][] = $arrayCallable;
    }

    private function addMethod(ClassMethod $classMethod): void
    {
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

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
    private function addCall(Node $node): void
    {
        // one node can be of multiple-class types
        if ($node instanceof MethodCall) {
            $classType = $this->resolveNodeClassTypes($node->var);
        } else {
            /** @var StaticCall $node */
            $classType = $this->resolveNodeClassTypes($node->class);
        }

        // anonymous
        if ($classType instanceof MixedType) {
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
    private function findCallsByClassAndMethod(string $className, string $methodName): array
    {
        $methodName = strtolower($methodName);

        return $this->callsByTypeAndMethod[$className][$methodName] ?? $this->arrayCallablesByTypeAndMethod[$className][$methodName] ?? [];
    }

    private function isChildOrEqualClassLike(string $desiredClass, ?string $currentClassName): bool
    {
        if ($currentClassName === null) {
            return false;
        }

        if (! $this->reflectionProvider->hasClass($desiredClass)) {
            return false;
        }

        if (! $this->reflectionProvider->hasClass($currentClassName)) {
            return false;
        }

        $desiredClassReflection = $this->reflectionProvider->getClass($desiredClass);
        $currentClassReflection = $this->reflectionProvider->getClass($currentClassName);

        if (! $currentClassReflection->isSubclassOf($desiredClassReflection->getName())) {
            return false;
        }
        return $currentClassName !== $desiredClass;
    }

    /**
     * @return Interface_[]
     */
    private function findImplementersOfInterface(string $interface): array
    {
        $implementerInterfaces = [];

        foreach ($this->parsedNodeCollector->getInterfaces() as $interfaceNode) {
            $className = $interfaceNode->getAttribute(AttributeKey::CLASS_NAME);

            if (! $this->isChildOrEqualClassLike($interface, $className)) {
                continue;
            }

            $implementerInterfaces[] = $interfaceNode;
        }

        return $implementerInterfaces;
    }

    private function resolveNodeClassTypes(Node $node): Type
    {
        if ($node instanceof MethodCall && $node->var instanceof Variable && $node->var->name === 'this') {
            /** @var string|null $className */
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
            if ($className) {
                return new ObjectType($className);
            }

            return new MixedType();
        }

        if ($node instanceof MethodCall) {
            return $this->nodeTypeResolver->resolve($node->var);
        }

        return $this->nodeTypeResolver->resolve($node);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function addCallByType(Node $node, Type $classType, string $methodName): void
    {
        // PHP is case insensitive for method names
        $methodName = strtolower($methodName);

        if ($classType instanceof TypeWithClassName) {
            if ($classType instanceof ThisType) {
                $classType = $classType->getStaticObjectType();
            }

            $this->callsByTypeAndMethod[$classType->getClassName()][$methodName][] = $node;
            $this->addParentTypeWithClassName($classType, $node, $methodName);
        }

        if ($classType instanceof UnionType) {
            foreach ($classType->getTypes() as $unionedType) {
                if (! $unionedType instanceof ObjectType) {
                    continue;
                }

                $this->callsByTypeAndMethod[$unionedType->getClassName()][$methodName][] = $node;
            }
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function addParentTypeWithClassName(
        TypeWithClassName $typeWithClassName,
        Node $node,
        string $methodName
    ): void {
        // include also parent types
        if (! $typeWithClassName instanceof ObjectType) {
            return;
        }

        if (! $this->reflectionProvider->hasClass($typeWithClassName->getClassName())) {
            return;
        }

        $classReflection = $this->reflectionProvider->getClass($typeWithClassName->getClassName());
        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            $this->callsByTypeAndMethod[$ancestorClassReflection->getName()][$methodName][] = $node;
        }
    }
}
