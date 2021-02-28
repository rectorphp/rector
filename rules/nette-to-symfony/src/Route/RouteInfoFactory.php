<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Route;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NetteToSymfony\ValueObject\RouteInfo;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;

final class RouteInfoFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository,
        ValueResolver $valueResolver,
        ReflectionProvider $reflectionProvider
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeRepository = $nodeRepository;
        $this->reflectionProvider = $reflectionProvider;
    }

    public function createFromNode(Node $node): ?RouteInfo
    {
        if ($node instanceof New_) {
            if ($this->hasNoArg($node)) {
                return null;
            }
            return $this->createRouteInfoFromArgs($node);
        }

        // Route::create()
        if ($node instanceof StaticCall) {
            if (! isset($node->args[0])) {
                return null;
            }
            if (! isset($node->args[1])) {
                return null;
            }
            if (! $this->nodeNameResolver->isNames($node->name, ['get', 'head', 'post', 'put', 'patch', 'delete'])) {
                return null;
            }

            /** @var string $methodName */
            $methodName = $this->nodeNameResolver->getName($node->name);
            $uppercasedMethodName = strtoupper($methodName);

            $methods = [];
            if ($uppercasedMethodName !== null) {
                $methods[] = $uppercasedMethodName;
            }

            return $this->createRouteInfoFromArgs($node, $methods);
        }

        return null;
    }

    private function hasNoArg(New_ $new): bool
    {
        if (! isset($new->args[0])) {
            return true;
        }

        return ! isset($new->args[1]);
    }

    /**
     * @param New_|StaticCall $node
     * @param string[] $methods
     */
    private function createRouteInfoFromArgs(Node $node, array $methods = []): ?RouteInfo
    {
        $pathArgument = $node->args[0]->value;
        $routePath = $this->valueResolver->getValue($pathArgument);
        // route path is needed
        if ($routePath === null) {
            return null;
        }
        if (! is_string($routePath)) {
            return null;
        }

        $routePath = $this->normalizeArgumentWrappers($routePath);

        $targetNode = $node->args[1]->value;
        if ($targetNode instanceof ClassConstFetch) {
            return $this->createForClassConstFetch($node, $methods, $routePath);
        }

        if ($targetNode instanceof String_) {
            return $this->createForString($targetNode, $routePath);
        }

        return null;
    }

    private function normalizeArgumentWrappers(string $routePath): string
    {
        return str_replace(['<', '>'], ['{', '}'], $routePath);
    }

    /**
     * @param New_|StaticCall $node
     * @param string[] $methods
     */
    private function createForClassConstFetch(Node $node, array $methods, string $routePath): ?RouteInfo
    {
        /** @var ClassConstFetch $controllerMethodNode */
        $controllerMethodNode = $node->args[1]->value;

        // SomePresenter::class
        if ($this->nodeNameResolver->isName($controllerMethodNode->name, 'class')) {
            $presenterClass = $this->nodeNameResolver->getName($controllerMethodNode->class);
            if ($presenterClass === null) {
                return null;
            }

            if (! $this->reflectionProvider->hasClass($presenterClass)) {
                return null;
            }

            $classReflection = $this->reflectionProvider->getClass($presenterClass);
            if ($classReflection->hasMethod('run')) {
                return new RouteInfo($presenterClass, 'run', $routePath, $methods);
            }
        }

        return null;
    }

    private function createForString(String_ $string, string $routePath): ?RouteInfo
    {
        $targetValue = $string->value;
        if (! Strings::contains($targetValue, ':')) {
            return null;
        }

        [$controller, $method] = explode(':', $targetValue);

        // detect class by controller name?
        // foreach all instance and try to match a name $controller . 'Presenter/Controller'

        $class = $this->nodeRepository->findByShortName($controller . 'Presenter');
        if (! $class instanceof Class_) {
            $class = $this->nodeRepository->findByShortName($controller . 'Controller');
        }

        // unable to find here
        if (! $class instanceof Class_) {
            return null;
        }

        $controllerClass = $this->nodeNameResolver->getName($class);
        if ($controllerClass === null) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($controllerClass)) {
            return null;
        }

        $controllerClassReflection = $this->reflectionProvider->getClass($controllerClass);

        $renderMethodName = 'render' . ucfirst($method);
        if ($controllerClassReflection->hasMethod($renderMethodName)) {
            return new RouteInfo($controllerClass, $renderMethodName, $routePath, []);
        }

        $actionMethodName = 'action' . ucfirst($method);
        if ($controllerClassReflection->hasMethod($actionMethodName)) {
            return new RouteInfo($controllerClass, $actionMethodName, $routePath, []);
        }

        return null;
    }
}
