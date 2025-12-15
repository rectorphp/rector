<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use Exception;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Rector\Symfony\CodeQuality\NodeAnalyzer\ParamConverterClassesResolver;
use Rector\Symfony\Enum\FosClass;
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
     * @var string[]
     */
    private const COMMON_ENTITY_CONTAINS_SUBNAMESPACES = ["\\Entity", "\\Document", "\\Model"];
    public function __construct(ControllerAnalyzer $controllerAnalyzer, ControllerMethodAnalyzer $controllerMethodAnalyzer, ClassDependencyManipulator $classDependencyManipulator, StaticTypeMapper $staticTypeMapper, ParamConverterClassesResolver $paramConverterClassesResolver, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->paramConverterClassesResolver = $paramConverterClassesResolver;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change Symfony controller method injection to direct constructor dependency, to separate params and services clearly', [new CodeSample(<<<'CODE_SAMPLE'
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
                if ($this->isNames($param->type, array_merge([SymfonyClass::USER_INTERFACE, SymfonyClass::REQUEST, FosClass::PARAM_FETCHER, SymfonyClass::UUID, Throwable::class, Exception::class], $entityClasses))) {
                    continue;
                }
                if ($this->isObjectType($param->type, new ObjectType(SymfonyClass::USER_INTERFACE))) {
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
                unset($classMethod->params[$key]);
                $propertyMetadatas[] = new PropertyMetadata($this->getName($param->var), $paramType);
            }
        }
        // nothing to move
        if ($propertyMetadatas === []) {
            return null;
        }
        $paramNamesToReplace = [];
        foreach ($propertyMetadatas as $propertyMetadata) {
            $paramNamesToReplace[] = $propertyMetadata->getName();
        }
        // 1. update constructor
        foreach ($propertyMetadatas as $propertyMetadata) {
            $this->classDependencyManipulator->addConstructorDependency($node, $propertyMetadata);
        }
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            $this->replaceParamUseWithPropertyFetch($classMethod, $paramNamesToReplace);
        }
        return $node;
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
