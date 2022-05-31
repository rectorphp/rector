<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpMethodReflection;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Laravel\NodeFactory\RouterRegisterNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20220531\Webmozart\Assert\Assert;
/**
 * @see https://laravel.com/docs/8.x/upgrade#automatic-controller-namespace-prefixing
 *
 * @see \Rector\Laravel\Tests\Rector\StaticCall\RouteActionCallableRector\RouteActionCallableRectorTest
 */
final class RouteActionCallableRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ROUTES = 'routes';
    /**
     * @var string
     */
    public const NAMESPACE = 'namespace';
    /**
     * @var string
     */
    private const DEFAULT_NAMESPACE = 'App\\Http\\Controllers';
    /**
     * @var string
     */
    private $namespace = self::DEFAULT_NAMESPACE;
    /**
     * @var array<string, string>
     */
    private $routes = [];
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\Laravel\NodeFactory\RouterRegisterNodeAnalyzer
     */
    private $routerRegisterNodeAnalyzer;
    public function __construct(\Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\Laravel\NodeFactory\RouterRegisterNodeAnalyzer $routerRegisterNodeAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->routerRegisterNodeAnalyzer = $routerRegisterNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use PHP callable syntax instead of string syntax for controller route declarations.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
Route::get('/users', 'UserController@index');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Route::get('/users', [\App\Http\Controllers\UserController::class, 'index']);
CODE_SAMPLE
, [self::NAMESPACE => 'App\\Http\\Controllers'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param Node\Expr\MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->routerRegisterNodeAnalyzer->isRegisterMethodStaticCall($node)) {
            return null;
        }
        $position = $this->getActionPosition($node->name);
        if (!isset($node->args[$position])) {
            return null;
        }
        if (!$node->args[$position] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $arg = $node->args[$position];
        $argValue = $this->valueResolver->getValue($arg->value);
        $segments = $this->resolveControllerFromAction($argValue);
        if ($segments === null) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $phpMethodReflection = $this->reflectionResolver->resolveMethodReflection($segments[0], $segments[1], $scope);
        if (!$phpMethodReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
            return null;
        }
        $node->args[$position]->value = $this->nodeFactory->createArray([$this->nodeFactory->createClassConstReference($segments[0]), $segments[1]]);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $routes = $configuration[self::ROUTES] ?? [];
        \RectorPrefix20220531\Webmozart\Assert\Assert::isArray($routes);
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString(\array_keys($routes));
        \RectorPrefix20220531\Webmozart\Assert\Assert::allString($routes);
        $this->routes = $routes;
        $namespace = $configuration[self::NAMESPACE] ?? self::DEFAULT_NAMESPACE;
        \RectorPrefix20220531\Webmozart\Assert\Assert::string($namespace);
        $this->namespace = $namespace;
    }
    /**
     * @return array{string, string}|null
     * @param mixed $action
     */
    private function resolveControllerFromAction($action) : ?array
    {
        if (!$this->isActionString($action)) {
            return null;
        }
        /** @var string $action */
        $segments = \explode('@', $action);
        if (\count($segments) !== 2) {
            return null;
        }
        [$controller, $method] = $segments;
        $namespace = $this->getNamespace($this->file->getSmartFileInfo());
        if (\strncmp($controller, '\\', \strlen('\\')) !== 0) {
            $controller = $namespace . '\\' . $controller;
        }
        return [$controller, $method];
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Expr $name
     */
    private function getActionPosition($name) : int
    {
        if ($this->routerRegisterNodeAnalyzer->isRegisterFallback($name)) {
            return 0;
        }
        if ($this->routerRegisterNodeAnalyzer->isRegisterMultipleVerbs($name)) {
            return 2;
        }
        return 1;
    }
    /**
     * @param mixed $action
     */
    private function isActionString($action) : bool
    {
        if (!\is_string($action)) {
            return \false;
        }
        return \strpos($action, '@') !== \false;
    }
    private function getNamespace(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo) : string
    {
        $realpath = $fileInfo->getRealPath();
        return $this->routes[$realpath] ?? $this->namespace;
    }
}
