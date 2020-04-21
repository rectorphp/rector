<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\ClassMethod;

use Nette\Application\IRouter;
use Nette\Application\Routers\RouteList;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocNode\Symfony\SymfonyRouteTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\RectorStrings;
use Rector\FrameworkMigration\Symfony\ImplicitToExplicitRoutingAnnotationDecorator;
use Rector\NetteToSymfony\Route\RouteInfoFactory;
use Rector\NetteToSymfony\ValueObject\RouteInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use ReflectionMethod;

/**
 * @see https://doc.nette.org/en/2.4/routing
 * @see https://symfony.com/doc/current/routing.html
 *
 * @see \Rector\NetteToSymfony\Tests\Rector\ClassMethod\RouterListToControllerAnnotationsRetor\RouterListToControllerAnnotationsRectorTest
 */
final class RouterListToControllerAnnotationsRector extends AbstractRector
{
    /**
     * @var RouteInfoFactory
     */
    private $routeInfoFactory;

    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    /**
     * @var ImplicitToExplicitRoutingAnnotationDecorator
     */
    private $implicitToExplicitRoutingAnnotationDecorator;

    public function __construct(
        RouteInfoFactory $routeInfoFactory,
        ReturnTypeInferer $returnTypeInferer,
        ImplicitToExplicitRoutingAnnotationDecorator $implicitToExplicitRoutingAnnotationDecorator
    ) {
        $this->routeInfoFactory = $routeInfoFactory;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->implicitToExplicitRoutingAnnotationDecorator = $implicitToExplicitRoutingAnnotationDecorator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change new Route() from RouteFactory to @Route annotation above controller method',
            [
                new CodeSample(
                    <<<'PHP'
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
PHP
                    ,
                    <<<'PHP'
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
PHP
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
        if (empty($node->stmts)) {
            return null;
        }

        $inferedReturnType = $this->returnTypeInferer->inferFunctionLike($node);

        $routeListObjectType = new ObjectType(RouteList::class);
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
            if ($classMethod === null) {
                continue;
            }

            $symfonyRoutePhpDocTagValueNode = $this->createSymfonyRoutePhpDocTagValueNode($routeInfo);

            $this->implicitToExplicitRoutingAnnotationDecorator->decorateClassMethodWithRouteAnnotation(
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

        return $this->betterNodeFinder->find($classMethod->stmts, function (Node $node): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            // $routeList[] =
            if (! $node->var instanceof ArrayDimFetch) {
                return false;
            }

            if ($this->isObjectType($node->expr, IRouter::class)) {
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
            if ($routeNameToControllerMethod === null) {
                continue;
            }

            $routeInfos[] = $routeNameToControllerMethod;
        }

        return $routeInfos;
    }

    private function resolveControllerClassMethod(RouteInfo $routeInfo): ?ClassMethod
    {
        $classNode = $this->classLikeParsedNodesFinder->findClass($routeInfo->getClass());
        if ($classNode === null) {
            return null;
        }

        return $classNode->getMethod($routeInfo->getMethod());
    }

    private function createSymfonyRoutePhpDocTagValueNode(RouteInfo $routeInfo): SymfonyRouteTagValueNode
    {
        return new SymfonyRouteTagValueNode($routeInfo->getPath(), [], null, $routeInfo->getHttpMethods());
    }

    private function completeImplicitRoutes(): void
    {
        $presenterClasses = $this->classLikeParsedNodesFinder->findClassesBySuffix('Presenter');

        foreach ($presenterClasses as $presenterClass) {
            foreach ($presenterClass->getMethods() as $classMethod) {
                if ($this->shouldSkipClassStmt($classMethod)) {
                    continue;
                }

                $path = $this->resolvePathFromClassAndMethodNodes($presenterClass, $classMethod);
                $symfonyRoutePhpDocTagValueNode = new SymfonyRouteTagValueNode($path);

                $this->implicitToExplicitRoutingAnnotationDecorator->decorateClassMethodWithRouteAnnotation(
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

        // @todo decouple - resolve method return type
        if (! method_exists($className, $methodName)) {
            return false;
        }

        $methodReflection = new ReflectionMethod($className, $methodName);
        if ($methodReflection->getReturnType() !== null) {
            $staticCallReturnType = (string) $methodReflection->getReturnType();
            if (is_a($staticCallReturnType, IRouter::class, true)) {
                return true;
            }
        }

        return false;
    }

    private function shouldSkipClassStmt(Node $node): bool
    {
        if (! $node instanceof ClassMethod) {
            return true;
        }

        // not an action method
        if (! $node->isPublic()) {
            return true;
        }

        if (! $this->isName($node, '#^(render|action)#')) {
            return true;
        }

        if ($node->getAttribute(ImplicitToExplicitRoutingAnnotationDecorator::HAS_ROUTE_ANNOTATION)) {
            return true;
        }

        // already has Route tag
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        return $phpDocInfo->hasByType(SymfonyRouteTagValueNode::class);
    }

    private function resolvePathFromClassAndMethodNodes(Class_ $classNode, ClassMethod $classMethod): string
    {
        /** @var string $presenterName */
        $presenterName = $this->getName($classNode);

        /** @var string $presenterPart */
        $presenterPart = Strings::after($presenterName, '\\', -1);

        /** @var string $presenterPart */
        $presenterPart = Strings::substring($presenterPart, 0, -Strings::length('Presenter'));
        $presenterPart = RectorStrings::camelCaseToDashes($presenterPart);

        $match = (array) Strings::match($this->getName($classMethod), '#^(action|render)(?<short_action_name>.*?$)#sm');
        $actionPart = lcfirst($match['short_action_name']);

        return $presenterPart . '/' . $actionPart;
    }
}
