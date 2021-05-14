<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Route;

use RectorPrefix20210514\Nette\Utils\Strings;
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
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeRepository = $nodeRepository;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function createFromNode(\PhpParser\Node $node) : ?\Rector\NetteToSymfony\ValueObject\RouteInfo
    {
        if ($node instanceof \PhpParser\Node\Expr\New_) {
            if ($this->hasNoArg($node)) {
                return null;
            }
            return $this->createRouteInfoFromArgs($node);
        }
        // Route::create()
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            if (!isset($node->args[0])) {
                return null;
            }
            if (!isset($node->args[1])) {
                return null;
            }
            if (!$this->nodeNameResolver->isNames($node->name, ['get', 'head', 'post', 'put', 'patch', 'delete'])) {
                return null;
            }
            /** @var string $methodName */
            $methodName = $this->nodeNameResolver->getName($node->name);
            $uppercasedMethodName = \strtoupper($methodName);
            $methods = [];
            if ($uppercasedMethodName !== null) {
                $methods[] = $uppercasedMethodName;
            }
            return $this->createRouteInfoFromArgs($node, $methods);
        }
        return null;
    }
    private function hasNoArg(\PhpParser\Node\Expr\New_ $new) : bool
    {
        if (!isset($new->args[0])) {
            return \true;
        }
        return !isset($new->args[1]);
    }
    /**
     * @param New_|StaticCall $node
     * @param string[] $methods
     */
    private function createRouteInfoFromArgs(\PhpParser\Node $node, array $methods = []) : ?\Rector\NetteToSymfony\ValueObject\RouteInfo
    {
        $pathArgument = $node->args[0]->value;
        $routePath = $this->valueResolver->getValue($pathArgument);
        // route path is needed
        if ($routePath === null) {
            return null;
        }
        if (!\is_string($routePath)) {
            return null;
        }
        $routePath = $this->normalizeArgumentWrappers($routePath);
        $targetNode = $node->args[1]->value;
        if ($targetNode instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return $this->createForClassConstFetch($node, $methods, $routePath);
        }
        if ($targetNode instanceof \PhpParser\Node\Scalar\String_) {
            return $this->createForString($targetNode, $routePath);
        }
        return null;
    }
    private function normalizeArgumentWrappers(string $routePath) : string
    {
        return \str_replace(['<', '>'], ['{', '}'], $routePath);
    }
    /**
     * @param New_|StaticCall $node
     * @param string[] $methods
     */
    private function createForClassConstFetch(\PhpParser\Node $node, array $methods, string $routePath) : ?\Rector\NetteToSymfony\ValueObject\RouteInfo
    {
        /** @var ClassConstFetch $controllerMethodNode */
        $controllerMethodNode = $node->args[1]->value;
        // SomePresenter::class
        if ($this->nodeNameResolver->isName($controllerMethodNode->name, 'class')) {
            $presenterClass = $this->nodeNameResolver->getName($controllerMethodNode->class);
            if ($presenterClass === null) {
                return null;
            }
            if (!$this->reflectionProvider->hasClass($presenterClass)) {
                return null;
            }
            $classReflection = $this->reflectionProvider->getClass($presenterClass);
            if ($classReflection->hasMethod('run')) {
                return new \Rector\NetteToSymfony\ValueObject\RouteInfo($presenterClass, 'run', $routePath, $methods);
            }
        }
        return null;
    }
    private function createForString(\PhpParser\Node\Scalar\String_ $string, string $routePath) : ?\Rector\NetteToSymfony\ValueObject\RouteInfo
    {
        $targetValue = $string->value;
        if (!\RectorPrefix20210514\Nette\Utils\Strings::contains($targetValue, ':')) {
            return null;
        }
        [$controller, $method] = \explode(':', $targetValue);
        // detect class by controller name?
        // foreach all instance and try to match a name $controller . 'Presenter/Controller'
        $class = $this->nodeRepository->findByShortName($controller . 'Presenter');
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            $class = $this->nodeRepository->findByShortName($controller . 'Controller');
        }
        // unable to find here
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $controllerClass = $this->nodeNameResolver->getName($class);
        if ($controllerClass === null) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($controllerClass)) {
            return null;
        }
        $controllerClassReflection = $this->reflectionProvider->getClass($controllerClass);
        $renderMethodName = 'render' . \ucfirst($method);
        if ($controllerClassReflection->hasMethod($renderMethodName)) {
            return new \Rector\NetteToSymfony\ValueObject\RouteInfo($controllerClass, $renderMethodName, $routePath, []);
        }
        $actionMethodName = 'action' . \ucfirst($method);
        if ($controllerClassReflection->hasMethod($actionMethodName)) {
            return new \Rector\NetteToSymfony\ValueObject\RouteInfo($controllerClass, $actionMethodName, $routePath, []);
        }
        return null;
    }
}
