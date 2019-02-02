<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Route;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Application\ClassLikeNodeCollector;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;

final class RouteInfoFactory
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var ClassLikeNodeCollector
     */
    private $classLikeNodeCollector;

    public function __construct(
        NameResolver $nameResolver,
        ValueResolver $valueResolver,
        ClassLikeNodeCollector $classLikeNodeCollector
    ) {
        $this->nameResolver = $nameResolver;
        $this->valueResolver = $valueResolver;
        $this->classLikeNodeCollector = $classLikeNodeCollector;
    }

    public function createFromNode(Node $expr): ?RouteInfo
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

            $method = $this->nameResolver->matchNameInsensitiveInMap($expr, [
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
        $routePath = $this->valueResolver->resolve($pathArgument);

        // route path is needed
        if ($routePath === null || ! is_string($routePath)) {
            return null;
        }

        $targetNode = $expr->args[1]->value;
        if ($targetNode instanceof ClassConstFetch) {
            /** @var ClassConstFetch $controllerMethodNode */
            $controllerMethodNode = $expr->args[1]->value;

            // SomePresenter::class
            if ($this->nameResolver->isName($controllerMethodNode->name, 'class')) {
                $presenterClass = $this->nameResolver->resolve($controllerMethodNode->class);
                if ($presenterClass === null) {
                    return null;
                }

                if (class_exists($presenterClass) === false) {
                    return null;
                }

                if (method_exists($presenterClass, 'run')) {
                    return new RouteInfo($presenterClass, 'run', $routePath, null, $methods);
                }
            }
            // @todo method specific route
        }

        if ($targetNode instanceof String_) {
            $targetValue = $targetNode->value;
            if (! Strings::contains($targetValue, ':')) {
                return null;
            }

            [$controller, $method] = explode(':', $targetValue);

            // detect class by controller name?
            // foreach all instance and try to match a name $controller . 'Presenter/Controller'

            $classNode = $this->classLikeNodeCollector->findByShortName($controller . 'Presenter');
            if ($classNode === null) {
                $classNode = $this->classLikeNodeCollector->findByShortName($controller . 'Controller');
            }

            // unable to find here
            if ($classNode === null) {
                return null;
            }

            $controllerClass = $this->nameResolver->resolve($classNode);
            if ($controllerClass === null) {
                return null;
            }

            $methodName = null;
            if (method_exists($controllerClass, 'render' . ucfirst($method))) {
                $methodName = 'render' . ucfirst($method);
            } elseif (method_exists($controllerClass, 'action' . ucfirst($method))) {
                $methodName = 'action' . ucfirst($method);
            }

            if ($methodName === null) {
                return null;
            }

            return new RouteInfo($controllerClass, $methodName, $routePath, null, []);
        }

        return null;
    }
}
