<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Symfony\SymfonyRouteTagValueNodeFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NetteToSymfony\Route\RouteInfoFactory;
use Rector\NetteToSymfony\Routing\ExplicitRouteAnnotationDecorator;
use Rector\NetteToSymfony\ValueObject\RouteInfo;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://doc.nette.org/en/2.4/routing
 * @see https://symfony.com/doc/current/routing.html
 *
 * @see \Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRector\RouterListToControllerAnnotationsRectorTest
 */
final class RouterListToControllerAnnotationsRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/qVlXk2/2
     */
    private const ACTION_RENDER_NAME_MATCHING_REGEX = '#^(action|render)(?<short_action_name>.*?$)#sm';

    /**
     * Package "nette/application" is required for DEV, might not exist for PROD.
     * So access the class throgh the string
     *
     * @var string
     */
    private const ROUTE_LIST_CLASS = 'Nette\Application\Routers\RouteList';

    /**
     * @var RouteInfoFactory
     */
    private $routeInfoFactory;

    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    /**
     * @var ExplicitRouteAnnotationDecorator
     */
    private $explicitRouteAnnotationDecorator;

    /**
     * @var SymfonyRouteTagValueNodeFactory
     */
    private $symfonyRouteTagValueNodeFactory;

    public function __construct(
        ExplicitRouteAnnotationDecorator $explicitRouteAnnotationDecorator,
        ReturnTypeInferer $returnTypeInferer,
        RouteInfoFactory $routeInfoFactory,
        SymfonyRouteTagValueNodeFactory $symfonyRouteTagValueNodeFactory
    ) {
        $this->routeInfoFactory = $routeInfoFactory;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->explicitRouteAnnotationDecorator = $explicitRouteAnnotationDecorator;
        $this->symfonyRouteTagValueNodeFactory = $symfonyRouteTagValueNodeFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change new Route() from RouteFactory to @Route annotation above controller method',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class RouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();
        $routeList[] = new Route('some-path', SomePresenter::class);

        return $routeList;
    }
}

final class SomePresenter
{
    public function run()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class RouterFactory
{
    public function create(): RouteList
    {
        $routeList = new RouteList();

        // case of single action controller, usually get() or __invoke() method
        $routeList[] = new Route('some-path', SomePresenter::class);

        return $routeList;
    }
}

use Symfony\Component\Routing\Annotation\Route;

final class SomePresenter
{
    /**
     * @Route(path="some-path")
     */
    public function run()
    {
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * List of nodes this class checks, classes that implement @see \PhpParser\Node
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        if ($node->stmts === []) {
            return null;
        }
        $inferedReturnType = $this->returnTypeInferer->inferFunctionLike($node);

        $routeListObjectType = new ObjectType(self::ROUTE_LIST_CLASS);
        if (! $inferedReturnType->isSuperTypeOf($routeListObjectType)->yes()) {
            return null;
        }

        $assignNodes = $this->resolveAssignRouteNodes($node);
        if ($assignNodes === []) {
            return null;
        }

        $routeInfos = $this->createRouteInfosFromAssignNodes($assignNodes);

        /** @var RouteInfo $routeInfo */
        foreach ($routeInfos as $routeInfo) {
            $classMethod = $this->resolveControllerClassMethod($routeInfo);
            if (! $classMethod instanceof ClassMethod) {
                continue;
            }

            $symfonyRoutePhpDocTagValueNode = $this->createSymfonyRoutePhpDocTagValueNode($routeInfo);

            $this->explicitRouteAnnotationDecorator->decorateClassMethodWithRouteAnnotation(
                $classMethod,
                $symfonyRoutePhpDocTagValueNode
            );
        }

        // complete all other non-explicit methods, from "<presenter>/<action>"
        $this->completeImplicitRoutes();

        // remove routes
        $this->removeNodes($assignNodes);

        return null;
    }

    /**
     * @return Assign[]
     */
    private function resolveAssignRouteNodes(ClassMethod $classMethod): array
    {
        // look for <...>[] = IRoute<Type>

        return $this->betterNodeFinder->find((array) $classMethod->stmts, function (Node $node): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            // $routeList[] =
            if (! $node->var instanceof ArrayDimFetch) {
                return false;
            }

            if ($this->isObjectTypes($node->expr, ['Nette\Application\IRouter', 'Nette\Routing\Router'])) {
                return true;
            }

            if ($node->expr instanceof StaticCall) {
                // for custom static route factories
                return $this->isRouteStaticCallMatch($node->expr);
            }

            return false;
        });
    }

    /**
     * @param Assign[] $assignNodes
     * @return RouteInfo[]
     */
    private function createRouteInfosFromAssignNodes(array $assignNodes): array
    {
        $routeInfos = [];

        // collect annotations and target controllers
        foreach ($assignNodes as $assignNode) {
            $routeNameToControllerMethod = $this->routeInfoFactory->createFromNode($assignNode->expr);
            if (! $routeNameToControllerMethod instanceof \Rector\NetteToSymfony\ValueObject\RouteInfo) {
                continue;
            }

            $routeInfos[] = $routeNameToControllerMethod;
        }

        return $routeInfos;
    }

    private function resolveControllerClassMethod(RouteInfo $routeInfo): ?ClassMethod
    {
        $classNode = $this->nodeRepository->findClass($routeInfo->getClass());
        if (! $classNode instanceof Class_) {
            return null;
        }

        return $classNode->getMethod($routeInfo->getMethod());
    }

    private function createSymfonyRoutePhpDocTagValueNode(RouteInfo $routeInfo): SymfonyRouteTagValueNode
    {
        return $this->symfonyRouteTagValueNodeFactory->createFromItems([
            'path' => $routeInfo->getPath(),
            'methods' => $routeInfo->getHttpMethods(),
        ]);
    }

    private function completeImplicitRoutes(): void
    {
        $presenterClasses = $this->nodeRepository->findClassesBySuffix('Presenter');

        foreach ($presenterClasses as $presenterClass) {
            foreach ($presenterClass->getMethods() as $classMethod) {
                if ($this->shouldSkipClassMethod($classMethod)) {
                    continue;
                }

                $path = $this->resolvePathFromClassAndMethodNodes($presenterClass, $classMethod);
                $symfonyRoutePhpDocTagValueNode = $this->symfonyRouteTagValueNodeFactory->createFromItems([
                    'path' => $path,
                ]);

                $this->explicitRouteAnnotationDecorator->decorateClassMethodWithRouteAnnotation(
                    $classMethod,
                    $symfonyRoutePhpDocTagValueNode
                );
            }
        }
    }

    private function isRouteStaticCallMatch(StaticCall $staticCall): bool
    {
        $className = $this->getName($staticCall->class);
        if ($className === null) {
            return false;
        }

        $methodName = $this->getName($staticCall->name);
        if ($methodName === null) {
            return false;
        }

        if (! method_exists($className, $methodName)) {
            return false;
        }

        $reflectionMethod = new ReflectionMethod($className, $methodName);
        if ($reflectionMethod->getReturnType() === null) {
            return false;
        }

        $staticCallReturnType = (string) $reflectionMethod->getReturnType();
        return is_a($staticCallReturnType, 'Nette\Application\IRouter', true);
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        // not an action method
        if (! $classMethod->isPublic()) {
            return true;
        }

        if (! $this->isName($classMethod, '#^(render|action)#')) {
            return true;
        }
        $hasRouteAnnotation = $classMethod->getAttribute(ExplicitRouteAnnotationDecorator::HAS_ROUTE_ANNOTATION);

        if ($hasRouteAnnotation) {
            return true;
        }

        // already has Route tag
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasByType(SymfonyRouteTagValueNode::class);
    }

    private function resolvePathFromClassAndMethodNodes(Class_ $class, ClassMethod $classMethod): string
    {
        /** @var string $presenterName */
        $presenterName = $this->getName($class);

        /** @var string $presenterPart */
        $presenterPart = Strings::after($presenterName, '\\', -1);

        $presenterPart = Strings::substring($presenterPart, 0, -Strings::length('Presenter'));
        $presenterPart = StaticRectorStrings::camelCaseToDashes($presenterPart);

        $match = (array) Strings::match($this->getName($classMethod), self::ACTION_RENDER_NAME_MATCHING_REGEX);
        $actionPart = lcfirst($match['short_action_name']);

        return $presenterPart . '/' . $actionPart;
    }
}
