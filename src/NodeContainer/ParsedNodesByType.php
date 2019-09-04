<?php declare(strict_types=1);

namespace Rector\NodeContainer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;
use ReflectionClass;

/**
 * All parsed nodes grouped type
 */
final class ParsedNodesByType
{
    /**
     * @var string[]
     */
    private $collectableNodeTypes = [
        Class_::class,
        Interface_::class,
        ClassConst::class,
        ClassConstFetch::class,
        Trait_::class,
        ClassMethod::class,
        Function_::class,
        // simply collected
        New_::class,
        StaticCall::class,
        MethodCall::class,
        // for array callable - [$this, 'someCall']
        Array_::class,
    ];

    /**
     * @var Class_[]
     */
    private $classes = [];

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ClassConst[][]
     */
    private $constantsByType = [];

    /**
     * @var ClassConstFetch[]
     */
    private $classConstantFetches = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ClassMethod[][]
     */
    private $methodsByType = [];

    /**
     * @var Node[][]
     */
    private $simpleParsedNodesByType = [];

    /**
     * @var MethodCall[][][]|StaticCall[][][]
     */
    private $methodsCallsByTypeAndMethod = [];

    /**
     * E.g. [$this, 'someLocalMethod']
     * @var Array_[][][]
     */
    private $arrayCallablesByTypeAndMethod = [];

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    /**
     * @return Node[]
     */
    public function getNodesByType(string $type): array
    {
        return $this->simpleParsedNodesByType[$type] ?? [];
    }

    /**
     * @return Class_[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @return New_[]
     */
    public function getNewNodes(): array
    {
        return $this->simpleParsedNodesByType[New_::class] ?? [];
    }

    /**
     * Due to circular reference
     * @required
     */
    public function setNodeTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function findClass(string $name): ?Class_
    {
        return $this->classes[$name] ?? null;
    }

    public function findInterface(string $name): ?Interface_
    {
        return $this->simpleParsedNodesByType[Interface_::class][$name] ?? null;
    }

    public function findTrait(string $name): ?Trait_
    {
        return $this->simpleParsedNodesByType[Trait_::class][$name] ?? null;
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
        foreach ($this->classes as $classNode) {
            $className = $classNode->getAttribute(AttributeKey::CLASS_NAME);
            if ($className === null) {
                return [];
            }

            if (! is_a($className, $class, true)) {
                continue;
            }

            if ($className === $class) {
                continue;
            }

            $childrenClasses[] = $classNode;
        }

        return $childrenClasses;
    }

    /**
     * @return Interface_[]
     */
    public function findImplementersOfInterface(string $interface): array
    {
        $implementerInterfaces = [];
        foreach ($this->simpleParsedNodesByType[Interface_::class] ?? [] as $interfaceNode) {
            $className = $interfaceNode->getAttribute(AttributeKey::CLASS_NAME);
            if ($className === null) {
                return [];
            }

            if (! is_a($className, $interface, true)) {
                continue;
            }

            if ($className === $interface) {
                continue;
            }

            $implementerInterfaces[] = $interfaceNode;
        }

        return $implementerInterfaces;
    }

    /**
     * @return Trait_[]
     */
    public function findUsedTraitsInClass(ClassLike $classLike): array
    {
        $traits = [];

        foreach ($classLike->getTraitUses() as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitName = $this->nameResolver->getName($trait);
                if ($traitName === null) {
                    continue;
                }

                $foundTrait = $this->findTrait($traitName);
                if ($foundTrait !== null) {
                    $traits[] = $foundTrait;
                }
            }
        }

        return $traits;
    }

    public function findByShortName(string $shortName): ?Class_
    {
        foreach ($this->classes as $className => $classNode) {
            if (Strings::endsWith($className, '\\' . $shortName)) {
                return $classNode;
            }
        }

        return null;
    }

    /**
     * @return Class_[]
     */
    public function findClassesBySuffix(string $suffix): array
    {
        $classNodes = [];

        foreach ($this->classes as $className => $classNode) {
            if (! Strings::endsWith($className, $suffix)) {
                continue;
            }

            $classNodes[] = $classNode;
        }

        return $classNodes;
    }

    public function hasClassChildren(string $class): bool
    {
        return $this->findChildrenOfClass($class) !== [];
    }

    /**
     * @return Class_|Interface_|null
     */
    public function findClassOrInterface(string $type): ?ClassLike
    {
        $class = $this->findClass($type);
        if ($class !== null) {
            return $class;
        }

        return $this->findInterface($type);
    }

    public function findClassConstant(string $className, string $constantName): ?ClassConst
    {
        if (Strings::contains($constantName, '\\')) {
            throw new ShouldNotHappenException(sprintf('Switched arguments in "%s"', __METHOD__));
        }

        return $this->constantsByType[$className][$constantName] ?? null;
    }

    /**
     * @return ClassConstFetch[]
     */
    public function getClassConstantFetches(): array
    {
        return $this->classConstantFetches;
    }

    public function findFunction(string $name): ?Function_
    {
        return $this->simpleParsedNodesByType[Function_::class][$name] ?? null;
    }

    public function findMethod(string $methodName, string $className): ?ClassMethod
    {
        if (isset($this->methodsByType[$className][$methodName])) {
            return $this->methodsByType[$className][$methodName];
        }

        $parentClass = $className;
        while ($parentClass = get_parent_class($parentClass)) {
            if (isset($this->methodsByType[$parentClass][$methodName])) {
                return $this->methodsByType[$parentClass][$methodName];
            }
        }

        return null;
    }

    public function isStaticMethod(string $methodName, string $className): bool
    {
        $methodNode = $this->findMethod($methodName, $className);
        if ($methodNode !== null) {
            return $methodNode->isStatic();
        }

        // could be static in doc type magic
        // @see https://regex101.com/r/tlvfTB/1
        if (class_exists($className) || trait_exists($className)) {
            $reflectionClass = new ReflectionClass($className);
            if (Strings::match(
                (string) $reflectionClass->getDocComment(),
                '#@method\s*static\s*(.*?)\b' . $methodName . '\b#'
            )) {
                return true;
            }

            $methodReflection = $reflectionClass->getMethod($methodName);
            return $methodReflection->isStatic();
        }

        return false;
    }

    public function isCollectableNode(Node $node): bool
    {
        foreach ($this->collectableNodeTypes as $collectableNodeType) {
            if (is_a($node, $collectableNodeType, true)) {
                return true;
            }
        }

        return false;
    }

    public function collect(Node $node): void
    {
        if ($node instanceof Class_) {
            $this->addClass($node);
            return;
        }

        if ($node instanceof Interface_ || $node instanceof Trait_ || $node instanceof Function_) {
            $name = $this->nameResolver->getName($node);
            if ($name === null) {
                throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
            }

            $nodeClass = get_class($node);
            $this->simpleParsedNodesByType[$nodeClass][$name] = $node;
            return;
        }

        if ($node instanceof ClassConst) {
            $this->addClassConstant($node);
            return;
        }

        if ($node instanceof ClassConstFetch) {
            $this->addClassConstantFetch($node);
            return;
        }

        if ($node instanceof ClassMethod) {
            $this->addMethod($node);
            return;
        }

        // array callable - [$this, 'someCall']
        if ($node instanceof Array_) {
            $arrayCallableClassAndMethod = $this->matchArrayCallableClassAndMethod($node);
            if ($arrayCallableClassAndMethod === null) {
                return;
            }

            [$className, $methodName] = $arrayCallableClassAndMethod;
            if (! method_exists($className, $methodName)) {
                return;
            }

            $this->arrayCallablesByTypeAndMethod[$className][$methodName][] = $node;
            return;
        }

        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $this->addCall($node);
            return;
        }

        // simple collect
        $type = get_class($node);
        $this->simpleParsedNodesByType[$type][] = $node;
    }

    /**
     * @return MethodCall[]|StaticCall[]|Array_[]
     */
    public function findClassMethodCalls(ClassMethod $classMethod): array
    {
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) { // anonymous
            return [];
        }

        $methodName = $this->nameResolver->getName($classMethod);
        if ($methodName === null) {
            return [];
        }

        return $this->methodsCallsByTypeAndMethod[$className][$methodName] ?? $this->arrayCallablesByTypeAndMethod[$className][$methodName] ?? [];
    }

    /**
     * @return MethodCall[][]|StaticCall[][]
     */
    public function findMethodCallsOnClass(string $className): array
    {
        return $this->methodsCallsByTypeAndMethod[$className] ?? [];
    }

    /**
     * @return New_[]
     */
    public function findNewNodesByClass(string $className): array
    {
        $newNodesByClass = [];
        foreach ($this->getNewNodes() as $newNode) {
            if ($this->nameResolver->isName($newNode->class, $className)) {
                $newNodesByClass[] = $newNode;
            }
        }

        return $newNodesByClass;
    }

    private function addClass(Class_ $classNode): void
    {
        if ($this->isClassAnonymous($classNode)) {
            return;
        }

        $name = $classNode->getAttribute(AttributeKey::CLASS_NAME);
        if ($name === null) {
            throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
        }

        $this->classes[$name] = $classNode;
    }

    private function addClassConstant(ClassConst $classConst): void
    {
        $className = $classConst->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
        }

        $constantName = $this->nameResolver->getName($classConst);

        $this->constantsByType[$className][$constantName] = $classConst;
    }

    private function addClassConstantFetch(ClassConstFetch $classConstFetch): void
    {
        $this->classConstantFetches[] = $classConstFetch;
    }

    private function addMethod(ClassMethod $classMethod): void
    {
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) { // anonymous
            return;
        }

        $methodName = $this->nameResolver->getName($classMethod);
        $this->methodsByType[$className][$methodName] = $classMethod;
    }

    private function isClassAnonymous(Class_ $classNode): bool
    {
        if ($classNode->isAnonymous() || $classNode->name === null) {
            return true;
        }

        // PHPStan polution
        return Strings::startsWith($classNode->name->toString(), 'AnonymousClass');
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function addCall(Node $node): void
    {
        // one node can be of multiple-class types
        if ($node instanceof MethodCall) {
            if ($node->var instanceof MethodCall) {
                $classType = $this->resolveNodeClassTypes($node);
            } else {
                $classType = $this->resolveNodeClassTypes($node->var);
            }
        } else {
            $classType = $this->resolveNodeClassTypes($node->class);
        }

        $methodName = $this->nameResolver->getName($node);
        if ($classType instanceof MixedType) { // anonymous
            return;
        }

        if ($methodName === null) {
            return;
        }

        if ($classType instanceof ObjectType) {
            $this->methodsCallsByTypeAndMethod[$classType->getClassName()][$methodName][] = $node;
        }

        if ($classType instanceof UnionType) {
            foreach ($classType->getTypes() as $unionedType) {
                if (! $unionedType instanceof ObjectType) {
                    continue;
                }

                $this->methodsCallsByTypeAndMethod[$unionedType->getClassName()][$methodName][] = $node;
            }
        }
    }

    /**
     * Matches array like: "[$this, 'methodName']" → ['ClassName', 'methodName']
     * @return string[]|null
     */
    private function matchArrayCallableClassAndMethod(Array_ $array): ?array
    {
        if (count($array->items) !== 2) {
            return null;
        }

        if ($array->items[0] === null) {
            return null;
        }

        // $this, self, static, FQN
        if (! $this->isThisVariable($array->items[0]->value)) {
            return null;
        }

        if ($array->items[1] === null) {
            return null;
        }

        if (! $array->items[1]->value instanceof String_) {
            return null;
        }

        /** @var String_ $string */
        $string = $array->items[1]->value;

        $methodName = $string->value;
        $className = $array->getAttribute(AttributeKey::CLASS_NAME);

        if ($className === null) {
            return null;
        }

        return [$className, $methodName];
    }

    private function isThisVariable(Node $node): bool
    {
        // $this
        if ($node instanceof Variable && $this->nameResolver->isName($node, 'this')) {
            return true;
        }

        if ($node instanceof ClassConstFetch) {
            if (! $this->nameResolver->isName($node->name, 'class')) {
                return false;
            }

            // self::class, static::class
            if ($this->nameResolver->isNames($node->class, ['self', 'static'])) {
                return true;
            }

            /** @var string $className */
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);

            return $this->nameResolver->isName($node->class, $className);
        }

        return false;
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
}
