<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\Symfony\Bridge\NodeAnalyzer\ControllerMethodAnalyzer;
use Rector\Symfony\Enum\FosClass;
use Rector\Symfony\Enum\SensioAttribute;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    private AttributeFinder $attributeFinder;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @var string[]
     */
    private const COMMON_ENTITY_CONTAINS_SUBNAMESPACES = ["\\Entity", "\\Document", "\\Model"];
    public function __construct(ControllerAnalyzer $controllerAnalyzer, ControllerMethodAnalyzer $controllerMethodAnalyzer, ClassDependencyManipulator $classDependencyManipulator, StaticTypeMapper $staticTypeMapper, AttributeFinder $attributeFinder, ValueResolver $valueResolver)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->controllerMethodAnalyzer = $controllerMethodAnalyzer;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->attributeFinder = $attributeFinder;
        $this->valueResolver = $valueResolver;
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
        $propertyMetadatas = [];
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            $entityClasses = $this->resolveParamConverterEntityClasses($classMethod);
            foreach ($classMethod->getParams() as $key => $param) {
                // skip scalar and empty values, as not services
                if ($param->type === null || !$param->type instanceof FullyQualified) {
                    continue;
                }
                // request is allowed
                if ($this->isNames($param->type, [SymfonyClass::REQUEST, FosClass::PARAM_FETCHER])) {
                    continue;
                }
                if ($this->isNames($param->type, $entityClasses)) {
                    continue;
                }
                foreach (self::COMMON_ENTITY_CONTAINS_SUBNAMESPACES as $commonEntityContainsNamespace) {
                    if (strpos($this->getName($param->type), $commonEntityContainsNamespace) !== \false) {
                        continue 2;
                    }
                }
                // @todo allow parameter converter
                unset($classMethod->params[$key]);
                $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
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
            // replace param use with property fetch
            $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use ($paramNamesToReplace) {
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
                $propertyName = $this->getName($node);
                return new PropertyFetch(new Variable('this'), $propertyName);
            });
        }
        // 2. replace in method bodies
        return $node;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($classMethod->isMagic() && !$this->isName($classMethod->name, MethodName::INVOKE)) {
            return \true;
        }
        return !$this->controllerMethodAnalyzer->isAction($classMethod);
    }
    /**
     * @return string[]
     */
    private function resolveParamConverterEntityClasses(ClassMethod $classMethod): array
    {
        $entityClasses = [];
        $paramConverterAttributes = $this->attributeFinder->findManyByClass($classMethod, SensioAttribute::PARAM_CONVERTER);
        foreach ($paramConverterAttributes as $paramConverterAttribute) {
            foreach ($paramConverterAttribute->args as $arg) {
                if ($arg->name instanceof Identifier && $this->isName($arg->name, 'class')) {
                    $entityClass = $this->valueResolver->getValue($arg->value);
                    if (!is_string($entityClass)) {
                        continue;
                    }
                    $entityClasses[] = $entityClass;
                }
            }
        }
        return $entityClasses;
    }
}
