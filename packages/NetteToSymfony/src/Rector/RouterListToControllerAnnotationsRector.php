<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NetteToSymfony\Annotation\RouteTagValueNode;
use Rector\NetteToSymfony\Route\RouteInfo;
use Rector\NodeTypeResolver\Application\ClassLikeNodeCollector;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Maintainer\ClassMaintainer;
use Rector\PhpParser\Node\Maintainer\FunctionLikeMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionMethod;

/**
 * @see https://doc.nette.org/en/2.4/routing
 * @see https://symfony.com/doc/current/routing/requirements.html
 */
final class RouterListToControllerAnnotationsRector extends AbstractRector
{
    /**
     * @var string
     */
    private $routeListClass;

    /**
     * @var string
     */
    private $routerClass;

    /**
     * @var string
     */
    private $routeAnnotationClass;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var FunctionLikeMaintainer
     */
    private $functionLikeMaintainer;

    /**
     * @var ClassLikeNodeCollector
     */
    private $classLikeNodeCollector;

    /**
     * @var ClassMaintainer
     */
    private $classMaintainer;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        FunctionLikeMaintainer $functionLikeMaintainer,
        ClassLikeNodeCollector $classLikeNodeCollector,
        ClassMaintainer $classMaintainer,
        DocBlockAnalyzer $docBlockAnalyzer,
        string $routeListClass = 'Nette\Application\Routers\RouteList',
        string $routerClass = 'Nette\Application\IRouter',
        string $routeAnnotationClass = 'Symfony\Component\Routing\Annotation\Route'
    ) {
        $this->routeListClass = $routeListClass;
        $this->routerClass = $routerClass;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->functionLikeMaintainer = $functionLikeMaintainer;
        $this->classLikeNodeCollector = $classLikeNodeCollector;
        $this->classMaintainer = $classMaintainer;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->routeAnnotationClass = $routeAnnotationClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
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

final class SomePresenter
{
    /**
     * @Symfony\Component\Routing\Annotation\Route(path="some-path")
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
        if ($node->stmts === null || $node->stmts === []) {
            return null;
        }

        if ($node->returnType !== null) {
            if (! $this->isType($node->returnType, $this->routeListClass)) {
                return null;
            }
        } else {
            $staticReturnType = $this->functionLikeMaintainer->resolveStaticReturnTypeInfo($node);
            if ($staticReturnType === null) {
                return null;
            }

            $getFqnTypeNode = $staticReturnType->getFqnTypeNode();
            if ($getFqnTypeNode === null) {
                return null;
            }

            if ($this->isName($getFqnTypeNode, $this->routeListClass)) {
                return null;
            }
        }

        // ok

        // look for <...>[] = IRoute<Type>
        $assignNodes = $this->betterNodeFinder->find($node->stmts, function (Node $node) {
            if (! $node instanceof Assign) {
                return false;
            }

            // $routeList[] =
            if (! $node->var instanceof ArrayDimFetch) {
                return false;
            }

            if (! $this->isType($node->var->var, $this->routeListClass)) {
                return false;
            }

            if ($this->isType($node->expr, $this->routerClass)) {
                return true;
            }

            if ($node->expr instanceof StaticCall) {
                $className = $this->getName($node->expr->class);
                if ($className === null) {
                    return false;
                }

                $methodName = $this->getName($node->expr->name);
                if ($methodName === null) {
                    return false;
                }

                // @todo decouple - resolve method return type
                if (! method_exists($className, $methodName)) {
                    return false;
                }

                $methodReflection = new ReflectionMethod($className, $methodName);
                if ($methodReflection->getReturnType()) {
                    $staticCallReturnType = (string) $methodReflection->getReturnType();
                    if (is_a($staticCallReturnType, $this->routerClass, true)) {
                        return true;
                    }
                }
            }

            return false;
        });

        if ($assignNodes === []) {
            return null;
        }

        $routeNamesToControllerMethods = [];

        // collect annotations and target controllers
        foreach ($assignNodes as $assignNode) {
            $routeNameToControllerMethod = $this->resolveRouteNameToControllerMethod($assignNode->expr);
            if ($routeNameToControllerMethod === null) {
                continue;
            }

            $routeNamesToControllerMethods[] = $routeNameToControllerMethod;

            $this->removeNode($assignNode);
        }

        /** @var RouteInfo $routeInfo */
        foreach ($routeNamesToControllerMethods as $routeInfo) {
            $classNode = $this->classLikeNodeCollector->findClass($routeInfo->getClass());
            if ($classNode === null) {
                continue;
            }

            $classMethod = $this->classMaintainer->getMethodByName($classNode, $routeInfo->getMethod());
            if ($classMethod === null) {
                continue;
            }

            $phpDocTagNode = new RouteTagValueNode(
                $this->routeAnnotationClass,
                $routeInfo->getPath(),
                null,
                $routeInfo->getHttpMethods()
            );
            $this->docBlockAnalyzer->addTag($classMethod, $phpDocTagNode);
        }

        return null;
    }

    private function resolveRouteNameToControllerMethod(Node $expr): ?RouteInfo
    {
        if ($expr instanceof New_) {
            if (! isset($expr->args[0]) || ! isset($expr->args[1])) {
                return null;
            }

            return $this->createRouteInfoFromArgs($expr);
        }

        // Route::create()
        if ($expr instanceof StaticCall) {
            if (! isset($expr->args[0]) || ! isset($expr->args[1])) {
                return null;
            }

            $method = $this->matchNameInsensitiveInMap($expr, [
                'get' => 'GET',
                'head' => 'HEAD',
                'post' => 'POST',
                'put' => 'PUT',
                'patch' => 'PATCH',
                'delete' => 'DELETE',
            ]);

            $methods = [];
            if ($method !== null) {
                $methods[] = $method;
            }

            return $this->createRouteInfoFromArgs($expr, $methods);
        }

        return null;
    }

    /**
     * @param New_|StaticCall $expr
     * @param string[] $methods
     */
    private function createRouteInfoFromArgs(Node $expr, array $methods = []): ?RouteInfo
    {
        $pathArgument = $expr->args[0]->value;

        if ($pathArgument instanceof ClassConstFetch) {
            if ($this->isName($pathArgument->class, 'self')) {
                if (! $this->isName($pathArgument->name, 'class')) {
                    // get constant value
                    $routePath = $this->getValue($pathArgument);
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            $routePath = $this->getValue($pathArgument);
        }

        // route path is needed
        if ($routePath === null) {
            return null;
        }

        if ($expr->args[1]->value instanceof ClassConstFetch) {
            /** @var ClassConstFetch $controllerMethodNode */
            $controllerMethodNode = $expr->args[1]->value;

            // SomePresenter::class
            if ($this->isName($controllerMethodNode->name, 'class')) {
                $presenterClass = $this->getName($controllerMethodNode->class);
                if ($presenterClass === null) {
                    return null;
                }

                if (class_exists($presenterClass) === false) {
                    return null;
                }

                if (method_exists($presenterClass, 'run')) {
                    return new RouteInfo($presenterClass, 'run', $routePath, null, $methods);
                }

                if (method_exists($presenterClass, '__invoke')) {
                    return new RouteInfo($presenterClass, '__invoke', $routePath, null, $methods);
                }
            }
            // @todo method specific route
        }

        return null;
    }
}
