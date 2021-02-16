<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodReferenceAnalyzer;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use ReflectionMethod;

/**
 * @rector-doc
 * This service contains all the parsed nodes. E.g. all the functions, method call, classes, static calls etc.
 * It's useful in case of context analysis, e.g. find all the usage of class method to detect, if the method is used.
 */
final class NodeRepository
{
    /**
     * @var array<string, ClassMethod[]>
     */
    private $classMethodsByType = [];

    /**
     * @var array<string, Function_>
     */
    private $functionsByName = [];

    /**
     * @var array<string, FuncCall[]>
     */
    private $funcCallsByName = [];

    /**
     * @var array<string, array<array<MethodCall|StaticCall>>>
     */
    private $callsByTypeAndMethod = [];

    /**
     * E.g. [$this, 'someLocalMethod']
     * @var ArrayCallable[][][]
     */
    private $arrayCallablesByTypeAndMethod = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ArrayCallableMethodReferenceAnalyzer
     */
    private $arrayCallableMethodReferenceAnalyzer;

    /**
     * @var ParsedPropertyFetchNodeCollector
     */
    private $parsedPropertyFetchNodeCollector;

    /**
     * @var ParsedClassConstFetchNodeCollector
     */
    private $parsedClassConstFetchNodeCollector;

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    /**
     * @var array<string, Attribute[]>
     */
    private $attributes = [];

    public function __construct(
        ArrayCallableMethodReferenceAnalyzer $arrayCallableMethodReferenceAnalyzer,
        ParsedPropertyFetchNodeCollector $parsedPropertyFetchNodeCollector,
        NodeNameResolver $nodeNameResolver,
        ParsedClassConstFetchNodeCollector $parsedClassConstFetchNodeCollector,
        ParsedNodeCollector $parsedNodeCollector,
        TypeUnwrapper $typeUnwrapper
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayCallableMethodReferenceAnalyzer = $arrayCallableMethodReferenceAnalyzer;
        $this->parsedPropertyFetchNodeCollector = $parsedPropertyFetchNodeCollector;
        $this->parsedClassConstFetchNodeCollector = $parsedClassConstFetchNodeCollector;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->typeUnwrapper = $typeUnwrapper;
    }

    /**
     * To prevent circular reference
     * @required
     */
    public function autowireNodeRepository(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function collect(Node $node): void
    {
        if ($node instanceof ClassMethod) {
            $this->addMethod($node);
            return;
        }

        // array callable - [$this, 'someCall']
        if ($node instanceof Array_) {
            $arrayCallable = $this->arrayCallableMethodReferenceAnalyzer->match($node);
            if (! $arrayCallable instanceof ArrayCallable) {
                return;
            }

            if (! $arrayCallable->isExistingMethod()) {
                return;
            }

            $this->arrayCallablesByTypeAndMethod[$arrayCallable->getClass()][$arrayCallable->getMethod()][] = $arrayCallable;

            return;
        }

        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $this->addCall($node);
        }

        if ($node instanceof Function_) {
            $functionName = $this->nodeNameResolver->getName($node);
            $this->functionsByName[$functionName] = $node;
        }

        if ($node instanceof FuncCall) {
            $functionName = $this->nodeNameResolver->getName($node);
            $this->funcCallsByName[$functionName][] = $node;
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

    public function findFunctionByFuncCall(FuncCall $funcCall): ?Function_
    {
        $functionName = $this->nodeNameResolver->getName($funcCall);
        if ($functionName === null) {
            return null;
        }

        return $this->findFunction($functionName);
    }

    /**
     * @return MethodCall[][]|StaticCall[][]
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
        return array_filter($calls, function (Node $node): bool {
            return $node instanceof StaticCall;
        });
    }

    /**
     * @return ClassMethod[]
     */
    public function findClassMethodByTypeAndMethod(string $desiredType, string $desiredMethodName): array
    {
        $classMethods = [];

        foreach ($this->classMethodsByType as $className => $classMethodByMethodName) {
            if (! is_a($className, $desiredType, true)) {
                continue;
            }

            if (! isset($classMethodByMethodName[$desiredMethodName])) {
                continue;
            }

            $classMethods[] = $classMethodByMethodName[$desiredMethodName];
        }

        return $classMethods;
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
        if (Strings::contains($methodName, '\\')) {
            $message = sprintf('Class and method arguments are switched in "%s"', __METHOD__);
            throw new ShouldNotHappenException($message);
        }

        if (isset($this->classMethodsByType[$className][$methodName])) {
            return $this->classMethodsByType[$className][$methodName];
        }

        $parentClass = $className;

        if (! class_exists($parentClass)) {
            return null;
        }

        while ($parentClass = get_parent_class($parentClass)) {
            if (isset($this->classMethodsByType[$parentClass][$methodName])) {
                return $this->classMethodsByType[$parentClass][$methodName];
            }
        }

        return null;
    }

    public function isFunctionUsed(string $functionName): bool
    {
        return isset($this->funcCallsByName[$functionName]);
    }

    /**
     * @return MethodCall[]
     */
    public function getMethodsCalls(): array
    {
        $calls = Arrays::flatten($this->callsByTypeAndMethod);

        return array_filter($calls, function (Node $node): bool {
            return $node instanceof MethodCall;
        });
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

        /** @var string $method */
        $method = $classMethod->getAttribute(AttributeKey::METHOD_NAME);

        return $this->findCallsByClassAndMethod($class, $method);
    }

    /**
     * @return string[]
     */
    public function findDirectClassConstantFetches(string $desiredClassName, string $desiredConstantName): array
    {
        $classConstantFetchByClassAndName = $this->parsedClassConstFetchNodeCollector->getClassConstantFetchByClassAndName();
        return $classConstantFetchByClassAndName[$desiredClassName][$desiredConstantName] ?? [];
    }

    /**
     * @return string[]
     */
    public function findIndirectClassConstantFetches(string $desiredClassName, string $desiredConstantName): array
    {
        $classConstantFetchByClassAndName = $this->parsedClassConstFetchNodeCollector->getClassConstantFetchByClassAndName();

        foreach ($classConstantFetchByClassAndName as $className => $classesByConstantName) {
            if (! isset($classesByConstantName[$desiredConstantName])) {
                continue;
            }

            // include child usages and parent usages
            if (! is_a($className, $desiredClassName, true) && ! is_a($desiredClassName, $className, true)) {
                continue;
            }

            if ($desiredClassName === $className) {
                continue;
            }

            return $classesByConstantName[$desiredConstantName];
        }

        return [];
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
            if (! Strings::endsWith($className, $suffix)) {
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

    public function findClassMethodConstructorByNew(New_ $new): ?ClassMethod
    {
        $className = $this->nodeTypeResolver->resolve($new->class);
        if (! $className instanceof TypeWithClassName) {
            return null;
        }

        $constructorClassMethod = $this->findClassMethod($className->getClassName(), MethodName::CONSTRUCT);
        if (! $constructorClassMethod instanceof ClassMethod) {
            return null;
        }

        if ($constructorClassMethod->getParams() === []) {
            return null;
        }

        return $constructorClassMethod;
    }

    public function findPropertyByPropertyFetch(PropertyFetch $propertyFetch): ?Property
    {
        $propertyCallerType = $this->nodeTypeResolver->getStaticType($propertyFetch->var);
        if (! $propertyCallerType instanceof TypeWithClassName) {
            return null;
        }

        $className = $this->nodeTypeResolver->getFullyQualifiedClassName($propertyCallerType);
        $class = $this->findClass($className);
        if (! $class instanceof Class_) {
            return null;
        }

        $propertyName = $this->nodeNameResolver->getName($propertyFetch->name);
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

    /**
     * @return New_[]
     */
    public function findNewsByClass(string $className): array
    {
        return $this->parsedNodeCollector->findNewsByClass($className);
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
     * @return Param[]
     */
    public function getParams(): array
    {
        return $this->parsedNodeCollector->getParams();
    }

    /**
     * @return New_[]
     */
    public function getNews(): array
    {
        return $this->parsedNodeCollector->getNews();
    }

    /**
     * @return StaticCall[]
     */
    public function getStaticCalls(): array
    {
        return $this->parsedNodeCollector->getStaticCalls();
    }

    /**
     * @return ClassConstFetch[]
     */
    public function getClassConstFetches(): array
    {
        return $this->parsedNodeCollector->getClassConstFetches();
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
        return $this->callsByTypeAndMethod[$className][$methodName] ?? $this->arrayCallablesByTypeAndMethod[$className][$methodName] ?? [];
    }

    private function isChildOrEqualClassLike(string $desiredClass, ?string $currentClassName): bool
    {
        if ($currentClassName === null) {
            return false;
        }

        if (! is_a($currentClassName, $desiredClass, true)) {
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

    private function resolveCallerClassName(MethodCall $methodCall): ?string
    {
        $callerType = $this->nodeTypeResolver->getStaticType($methodCall->var);
        $callerObjectType = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($callerType);
        if (! $callerObjectType instanceof TypeWithClassName) {
            return null;
        }

        return $callerObjectType->getClassName();
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
        if ($classType instanceof TypeWithClassName) {
            $this->callsByTypeAndMethod[$classType->getClassName()][$methodName][] = $node;
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
}
