<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NetteToSymfony\Annotation\RouteTagValueNode;
use Rector\NetteToSymfony\Route\RouteInfo;
use Rector\NetteToSymfony\Route\RouteInfoFactory;
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
 * @see https://symfony.com/doc/current/routing.html
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

    /**
     * @var RouteInfoFactory
     */
    private $routeInfoFactory;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        FunctionLikeMaintainer $functionLikeMaintainer,
        ClassLikeNodeCollector $classLikeNodeCollector,
        ClassMaintainer $classMaintainer,
        DocBlockAnalyzer $docBlockAnalyzer,
        RouteInfoFactory $routeInfoFactory,
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
        $this->routeInfoFactory = $routeInfoFactory;
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

        $nodeReturnType = $this->resolveClassMethodReturnType($node);
        if ($nodeReturnType === []) {
            return null;
        }

        if (! in_array($this->routeListClass, $nodeReturnType, true)) {
            return null;
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
            $routeNameToControllerMethod = $this->routeInfoFactory->createFromNode($assignNode->expr);
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

    /**
     * @return string[]
     */
    private function resolveClassMethodReturnType(ClassMethod $classMethodNode): array
    {
        if ($classMethodNode->returnType !== null) {
            return $this->getTypes($classMethodNode->returnType);
        }

        $staticReturnType = $this->functionLikeMaintainer->resolveStaticReturnTypeInfo($classMethodNode);
        if ($staticReturnType === null) {
            return [];
        }

        $getFqnTypeNode = $staticReturnType->getFqnTypeNode();
        if ($getFqnTypeNode === null) {
            return [];
        }

        $fqnTypeName = $this->getName($getFqnTypeNode);
        if ($fqnTypeName === null) {
            return [];
        }

        return [$fqnTypeName];
    }
}
