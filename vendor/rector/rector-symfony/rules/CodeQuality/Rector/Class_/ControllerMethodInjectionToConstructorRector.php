<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use Exception;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\NodeManipulator\ClassInsertManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Rector\Symfony\CodeQuality\NodeAnalyzer\ParamConverterClassesResolver;
use Rector\Symfony\Enum\FosClass;
use Rector\Symfony\Enum\SymfonyAttribute;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Rector\ValueObject\MethodName;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Throwable;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Class_\ControllerMethodInjectionToConstructorRector\ControllerMethodInjectionToConstructorRectorTest
 */
final class ControllerMethodInjectionToConstructorRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ControllerAnalyzer $controllerAnalyzer;
    /**
     * @readonly
     */
    private ControllerMethodAnalyzer $controllerMethodAnalyzer;
    /**
     * @readonly
     */
    private ClassDependencyManipulator $classDependencyManipulator;
    /**
     * @readonly
     */
    private ClassInsertManipulator $classInsertManipulator;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ParamConverterClassesResolver $paramConverterClassesResolver;
    /**
     * @readonly
     */
    private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @var string[]
     */
    private const COMMON_ENTITY_CONTAINS_SUBNAMESPACES = ["\\Entity\\", "\\Document\\", "\\Model\\"];
    /**
     * @var string
     */
    private const AUTOWIRE_METHOD_NAME = 'autowire';
    /**
     * Used when a parent class already defines autowire(), to avoid overriding it
     * @var string
     */
    private const FALLBACK_AUTOWIRE_METHOD_NAME = 'autowireServices';
    public function __construct(ControllerAnalyzer $controllerAnalyzer, ControllerMethodAnalyzer $controllerMethodAnalyzer, ClassDependencyManipulator $classDependencyManipulator, ClassInsertManipulator $classInsertManipulator, StaticTypeMapper $staticTypeMapper, ParamConverterClassesResolver $paramConverterClassesResolver, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, ReflectionResolver $reflectionResolver)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->paramConverterClassesResolver = $paramConverterClassesResolver;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change Symfony controller method injection to direct constructor dependency, to separate params and services clearly. If a parent class has a constructor, use #[Required] autowire() method instead, to avoid repeating all parent params', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends AbstractController
{
    #[Route('/some-path', name: 'some_name')]
    public function someAction(
        Request $request,
        SomeService $someService
    ) {
        $data = $someService->getData();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends AbstractController
{
    public function __construct(
        private readonly SomeService $someService
    ) {
    }

    #[Route('/some-path', name: 'some_name')]
    public function someAction(
        Request $request
    ) {
        $data = $this->someService->getData();
    }
}
CODE_SAMPLE
), new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends SomeParentControllerWithConstructor
{
    #[Route('/some-path', name: 'some_name')]
    public function someAction(SomeService $someService)
    {
        $data = $someService->getData();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

final class SomeController extends SomeParentControllerWithConstructor
{
    private SomeService $someService;

    #[Route('/some-path', name: 'some_name')]
    public function someAction()
    {
        $data = $this->someService->getData();
    }

    #[Required]
    public function autowire(SomeService $someService): void
    {
        $this->someService = $someService;
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
        if (!$this->controllerAnalyzer->isController($node)) {
            return null;
        }
        if ($node->isAbstract()) {
            return null;
        }
        $propertyMetadatas = [];
        $constructParamVariables = [];
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod instanceof ClassMethod) {
            foreach ($constructClassMethod->params as $param) {
                if ($param->type instanceof FullyQualified) {
                    $constructParamVariables[$param->type->toString()] = $this->getName($param->var);
                }
            }
        }
        /** @var array<array{ClassMethod, int}> $paramsToRemove */
        $paramsToRemove = [];
        /** @var array<string, string[]> $methodParamNamesToReplace */
        $methodParamNamesToReplace = [];
        /** @var array<string, int[]> $removedMethodArgPositions */
        $removedMethodArgPositions = [];
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            $entityClasses = $this->paramConverterClassesResolver->resolveEntityClasses($classMethod);
            foreach ($classMethod->getParams() as $key => $param) {
                // skip scalar and empty values, as not services
                if ($param->type === null || !$param->type instanceof FullyQualified) {
                    continue;
                }
                // most likely mapped by attribute or autowired with specific type
                if ($param->attrGroups !== []) {
                    continue;
                }
                // skip allowed known objects
                if ($this->isNames($param->type, array_merge([SymfonyClass::USER_INTERFACE, SymfonyClass::REQUEST, FosClass::PARAM_FETCHER, Throwable::class, Exception::class], $entityClasses))) {
                    continue;
                }
                if ($this->nodeTypeResolver->isObjectTypes($param->type, [
                    new ObjectType(SymfonyClass::USER_INTERFACE),
                    new ObjectType('DateTimeInterface'),
                    new ObjectType(SymfonyClass::UUID),
                    // event listener method, not a controller action
                    new ObjectType(SymfonyClass::EVENT),
                    // request-scoped, must stay in the action method
                    new ObjectType(SymfonyClass::SESSION_INTERFACRE),
                ])) {
                    continue;
                }
                foreach (self::COMMON_ENTITY_CONTAINS_SUBNAMESPACES as $commonEntityContainsNamespace) {
                    if (strpos($this->getName($param->type), $commonEntityContainsNamespace) !== \false) {
                        continue 2;
                    }
                }
                $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
                if (!$paramType instanceof ObjectType) {
                    continue;
                }
                if ($paramType->isEnum()->yes()) {
                    continue;
                }
                if ($constructParamVariables !== [] && in_array($this->getName($param->var), $constructParamVariables, \true) && !in_array($this->getName($param->type), array_keys($constructParamVariables), \true)) {
                    continue;
                }
                if ($this->hasConflictedParamName($node, $classMethod->name->toString(), $this->getName($param->var), $this->getName($param->type))) {
                    continue;
                }
                if ($this->isUsedInStaticClosureUse($classMethod, $this->getName($param->var))) {
                    continue;
                }
                $paramName = $this->getName($param->var);
                $paramsToRemove[] = [$classMethod, $key];
                $methodParamNamesToReplace[$classMethod->name->toString()][] = $paramName;
                $removedMethodArgPositions[$classMethod->name->toString()][] = $key;
                // the parent class already provides the very same service, re-use it instead of adding own one
                if ($this->hasAccessibleParentProperty($node, $paramName, $paramType)) {
                    continue;
                }
                $propertyMetadatas[$paramName] = new PropertyMetadata($paramName, $paramType);
            }
        }
        // nothing to move
        if ($paramsToRemove === []) {
            return null;
        }
        // defer param removal to after collection to avoid mutation during iteration
        foreach ($paramsToRemove as [$methodToModify, $paramKey]) {
            unset($methodToModify->params[$paramKey]);
        }
        // 1. add dependencies
        if ($propertyMetadatas !== []) {
            if ($this->hasParentConstructor($node)) {
                // constructor would have to repeat all parent params, use setter injection instead
                $this->addRequiredAutowireClassMethod($node, $propertyMetadatas);
            } else {
                foreach ($propertyMetadatas as $propertyMetadata) {
                    $this->classDependencyManipulator->addConstructorDependency($node, $propertyMetadata);
                }
            }
        }
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            $methodName = $classMethod->name->toString();
            if (!isset($methodParamNamesToReplace[$methodName])) {
                continue;
            }
            $this->replaceParamUseWithPropertyFetch($classMethod, $methodParamNamesToReplace[$methodName]);
        }
        $this->updateCallSitesForRemovedParams($node, $removedMethodArgPositions);
        return $node;
    }
    /**
     * @param array<string, int[]> $removedArgPositionsByMethod
     */
    private function updateCallSitesForRemovedParams(Class_ $class, array $removedArgPositionsByMethod): void
    {
        if ($removedArgPositionsByMethod === []) {
            return;
        }
        foreach ($class->getMethods() as $classMethod) {
            if ($classMethod->stmts === null) {
                continue;
            }
            $this->traverseNodesWithCallable($classMethod->stmts, function (Node $node) use ($removedArgPositionsByMethod): ?MethodCall {
                if (!$node instanceof MethodCall) {
                    return null;
                }
                if (!$node->var instanceof Variable) {
                    return null;
                }
                if (!$this->isName($node->var, 'this')) {
                    return null;
                }
                $methodName = $this->getName($node->name);
                if ($methodName === null || !isset($removedArgPositionsByMethod[$methodName])) {
                    return null;
                }
                $removedPositions = $removedArgPositionsByMethod[$methodName];
                rsort($removedPositions);
                foreach ($removedPositions as $removedPosition) {
                    unset($node->args[$removedPosition]);
                }
                $node->args = array_values($node->args);
                return $node;
            });
        }
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($classMethod->isMagic() && !$this->isName($classMethod->name, MethodName::INVOKE)) {
            return \true;
        }
        if (!$this->controllerMethodAnalyzer->isAction($classMethod)) {
            return \true;
        }
        return $this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod);
    }
    private function hasConflictedParamName(Class_ $class, string $currentClassMethodName, string $paramName, string $paramType): bool
    {
        foreach ($class->getMethods() as $classMethod) {
            if ($this->isName($classMethod, $currentClassMethodName)) {
                continue;
            }
            foreach ($classMethod->getParams() as $param) {
                if (!$param->var instanceof Variable) {
                    continue;
                }
                if (!$this->isName($param->var, $paramName)) {
                    continue;
                }
                return $param->type instanceof FullyQualified && !$this->isName($param->type, $paramType);
            }
        }
        return \false;
    }
    /**
     * Is there a protected/public property of the same name and compatible type in a parent class?
     */
    private function hasAccessibleParentProperty(Class_ $class, string $propertyName, ObjectType $objectType): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasNativeProperty($propertyName)) {
                continue;
            }
            $nativePropertyReflection = $parentClassReflection->getNativeProperty($propertyName);
            if ($nativePropertyReflection->isPrivate()) {
                continue;
            }
            return $objectType->isSuperTypeOf($nativePropertyReflection->getReadableType())->yes();
        }
        return \false;
    }
    /**
     * @param PropertyMetadata[] $propertyMetadatas
     */
    private function addRequiredAutowireClassMethod(Class_ $class, array $propertyMetadatas): void
    {
        $autowireMethodName = $this->resolveAutowireMethodName($class);
        $autowireClassMethod = $class->getMethod($autowireMethodName);
        $isNewClassMethod = !$autowireClassMethod instanceof ClassMethod;
        if (!$autowireClassMethod instanceof ClassMethod) {
            $autowireClassMethod = new ClassMethod(new Identifier($autowireMethodName), ['flags' => Modifiers::PUBLIC, 'returnType' => new Identifier('void'), 'attrGroups' => [new AttributeGroup([new Attribute(new FullyQualified(SymfonyAttribute::REQUIRED))])], 'stmts' => []]);
        }
        foreach ($propertyMetadatas as $propertyMetadata) {
            $propertyName = $propertyMetadata->getName();
            $propertyType = $propertyMetadata->getType();
            $property = $this->nodeFactory->createPrivatePropertyFromNameAndType($propertyName, $propertyType);
            $this->classInsertManipulator->addAsFirstMethod($class, $property);
            $autowireClassMethod->params[] = new Param(new Variable($propertyName), null, $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PARAM));
            $autowireClassMethod->stmts[] = new Expression(new Assign(new PropertyFetch(new Variable('this'), $propertyName), new Variable($propertyName)));
        }
        if ($isNewClassMethod) {
            $class->stmts[] = $autowireClassMethod;
        }
    }
    private function resolveAutowireMethodName(Class_ $class): string
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return self::AUTOWIRE_METHOD_NAME;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasNativeMethod(self::AUTOWIRE_METHOD_NAME)) {
                return self::FALLBACK_AUTOWIRE_METHOD_NAME;
            }
        }
        return self::AUTOWIRE_METHOD_NAME;
    }
    private function hasParentConstructor(Class_ $class): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $found = \false;
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasNativeMethod(MethodName::CONSTRUCT)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    private function isUsedInStaticClosureUse(ClassMethod $classMethod, string $paramName): bool
    {
        if ($classMethod->stmts === null) {
            return \false;
        }
        $found = \false;
        $this->traverseNodesWithCallable($classMethod->stmts, function (Node $node) use ($paramName, &$found): ?int {
            if (!$node instanceof Closure) {
                return null;
            }
            if (!$node->static) {
                return null;
            }
            foreach ($node->uses as $closureUse) {
                if ($this->isName($closureUse->var, $paramName)) {
                    $found = \true;
                    return NodeVisitor::STOP_TRAVERSAL;
                }
            }
            return null;
        });
        return $found;
    }
    /**
     * @param string[] $paramNamesToReplace
     */
    private function replaceParamUseWithPropertyFetch(ClassMethod $classMethod, array $paramNamesToReplace): void
    {
        if ($classMethod->stmts === null) {
            return;
        }
        $this->traverseNodesWithCallable($classMethod->stmts, function (Node $node) use ($paramNamesToReplace) {
            if ($node instanceof Closure) {
                foreach ($node->uses as $key => $closureUse) {
                    if ($this->isNames($closureUse->var, $paramNamesToReplace)) {
                        unset($node->uses[$key]);
                    }
                }
                return $node;
            }
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->isNames($node, $paramNamesToReplace)) {
                return null;
            }
            if ($node->getAttribute(AttributeKey::IS_BEING_ASSIGNED) === \true) {
                return null;
            }
            $propertyName = $this->getName($node);
            return new PropertyFetch(new Variable('this'), $propertyName);
        });
    }
}
