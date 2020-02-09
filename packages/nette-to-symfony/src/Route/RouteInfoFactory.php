<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Route;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;
use Rector\Core\PhpParser\Node\Resolver\NameResolver;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NetteToSymfony\ValueObject\RouteInfo;

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
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    public function __construct(
        NameResolver $nameResolver,
        ValueResolver $valueResolver,
        ParsedNodeCollector $parsedNodeCollector
    ) {
        $this->nameResolver = $nameResolver;
        $this->valueResolver = $valueResolver;
        $this->parsedNodeCollector = $parsedNodeCollector;
    }

    public function createFromNode(Node $node): ?RouteInfo
    {
        if ($node instanceof New_) {
            if (! isset($node->args[0]) || ! isset($node->args[1])) {
                return null;
            }

            return $this->createRouteInfoFromArgs($node);
        }

        // Route::create()
        if ($node instanceof StaticCall) {
            if (! isset($node->args[0]) || ! isset($node->args[1])) {
                return null;
            }

            if (! $this->nameResolver->isNames($node, ['get', 'head', 'post', 'put', 'patch', 'delete'])) {
                return null;
            }

            /** @var string $methodName */
            $methodName = $this->nameResolver->getName($node);
            $uppercasedMethodName = strtoupper($methodName);

            $methods = [];
            if ($uppercasedMethodName !== null) {
                $methods[] = $uppercasedMethodName;
            }

            return $this->createRouteInfoFromArgs($node, $methods);
        }

        return null;
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
        if ($routePath === null || ! is_string($routePath)) {
            return null;
        }

        $routePath = $this->normalizeArgumentWrappers($routePath);

        $targetNode = $node->args[1]->value;
        if ($targetNode instanceof ClassConstFetch) {
            /** @var ClassConstFetch $controllerMethodNode */
            $controllerMethodNode = $node->args[1]->value;

            // SomePresenter::class
            if ($this->nameResolver->isName($controllerMethodNode->name, 'class')) {
                $presenterClass = $this->nameResolver->getName($controllerMethodNode->class);
                if ($presenterClass === null) {
                    return null;
                }

                if (! class_exists($presenterClass)) {
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

            $classNode = $this->parsedNodeCollector->findByShortName($controller . 'Presenter');
            if ($classNode === null) {
                $classNode = $this->parsedNodeCollector->findByShortName($controller . 'Controller');
            }

            // unable to find here
            if ($classNode === null) {
                return null;
            }

            $controllerClass = $this->nameResolver->getName($classNode);
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

    private function normalizeArgumentWrappers(string $routePath): string
    {
        return str_replace(['<', '>'], ['{', '}'], $routePath);
    }
}
