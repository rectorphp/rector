<?php

declare (strict_types=1);
namespace Rector\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpDocParser\PhpParser\SmartPhpParser;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Reflection\MethodReflectionResolver;
use Rector\ValueObject\MethodName;
use Throwable;
/**
 * The nodes provided by this resolver is for read-only analysis only!
 * They are not part of node tree processed by Rector, so any changes will not make effect in final printed file.
 */
final class AstResolver
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\PhpParser\SmartPhpParser
     */
    private $smartPhpParser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Reflection\MethodReflectionResolver
     */
    private $methodReflectionResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * Parsing files is very heavy performance, so this will help to leverage it
     * The value can be also null, when no statements could be parsed from the file.
     *
     * @var array<string, Stmt[]|null>
     */
    private $parsedFileNodes = [];
    public function __construct(SmartPhpParser $smartPhpParser, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, NodeTypeResolver $nodeTypeResolver, MethodReflectionResolver $methodReflectionResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->smartPhpParser = $smartPhpParser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->methodReflectionResolver = $methodReflectionResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @api downgrade
     * @return \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Enum_|null
     */
    public function resolveClassFromName(string $className)
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        return $this->resolveClassFromClassReflection($classReflection);
    }
    public function resolveClassMethodFromMethodReflection(MethodReflection $methodReflection) : ?ClassMethod
    {
        $classReflection = $methodReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();
        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        $classLikeName = $classReflection->getName();
        $methodName = $methodReflection->getName();
        $classMethod = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use($classLikeName, $methodName, &$classMethod) : ?int {
            if (!$node instanceof ClassLike) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $classLikeName)) {
                return null;
            }
            $method = $node->getMethod($methodName);
            if ($method instanceof ClassMethod) {
                $classMethod = $method;
                return NodeTraverser::STOP_TRAVERSAL;
            }
            return null;
        });
        /** @var ClassMethod|null $classMethod */
        return $classMethod;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|null
     */
    public function resolveClassMethodOrFunctionFromCall($call, Scope $scope)
    {
        if ($call instanceof FuncCall) {
            return $this->resolveFunctionFromFuncCall($call, $scope);
        }
        return $this->resolveClassMethodFromCall($call);
    }
    public function resolveFunctionFromFunctionReflection(FunctionReflection $functionReflection) : ?Function_
    {
        if (!$functionReflection instanceof PhpFunctionReflection) {
            return null;
        }
        $fileName = $functionReflection->getFileName();
        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        $functionName = $functionReflection->getName();
        $functionNode = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use($functionName, &$functionNode) : ?int {
            if (!$node instanceof Function_) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $functionName)) {
                return null;
            }
            $functionNode = $node;
            return NodeTraverser::STOP_TRAVERSAL;
        });
        /** @var Function_|null $functionNode */
        return $functionNode;
    }
    /**
     * @param class-string $className
     */
    public function resolveClassMethod(string $className, string $methodName) : ?ClassMethod
    {
        $methodReflection = $this->methodReflectionResolver->resolveMethodReflection($className, $methodName, null);
        if (!$methodReflection instanceof MethodReflection) {
            return null;
        }
        $classMethod = $this->resolveClassMethodFromMethodReflection($methodReflection);
        if (!$classMethod instanceof ClassMethod) {
            return $this->locateClassMethodInTrait($methodName, $methodReflection);
        }
        return $classMethod;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\NullsafeMethodCall $call
     */
    public function resolveClassMethodFromCall($call) : ?ClassMethod
    {
        $callerStaticType = $call instanceof MethodCall || $call instanceof NullsafeMethodCall ? $this->nodeTypeResolver->getType($call->var) : $this->nodeTypeResolver->getType($call->class);
        if (!$callerStaticType instanceof TypeWithClassName) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($call->name);
        if ($methodName === null) {
            return null;
        }
        return $this->resolveClassMethod($callerStaticType->getClassName(), $methodName);
    }
    /**
     * @return \PhpParser\Node\Stmt\Trait_|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Stmt\Enum_|null
     */
    public function resolveClassFromClassReflection(ClassReflection $classReflection)
    {
        if ($classReflection->isBuiltin()) {
            return null;
        }
        $fileName = $classReflection->getFileName();
        $stmts = $this->parseFileNameToDecoratedNodes($fileName);
        $className = $classReflection->getName();
        /** @var Class_|Trait_|Interface_|Enum_|null $classLike */
        $classLike = $this->betterNodeFinder->findFirst($stmts, function (Node $node) use($className) : bool {
            if (!$node instanceof ClassLike) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $className);
        });
        return $classLike;
    }
    /**
     * @return Trait_[]
     */
    public function parseClassReflectionTraits(ClassReflection $classReflection) : array
    {
        /** @var ClassReflection[] $classLikes */
        $classLikes = $classReflection->getTraits(\true);
        $traits = [];
        foreach ($classLikes as $classLike) {
            $fileName = $classLike->getFileName();
            $nodes = $this->parseFileNameToDecoratedNodes($fileName);
            $traitName = $classLike->getName();
            $traitNode = null;
            $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use($traitName, &$traitNode) : ?int {
                if (!$node instanceof Trait_) {
                    return null;
                }
                if (!$this->nodeNameResolver->isName($node, $traitName)) {
                    return null;
                }
                $traitNode = $node;
                return NodeTraverser::STOP_TRAVERSAL;
            });
            if (!$traitNode instanceof Trait_) {
                continue;
            }
            $traits[] = $traitNode;
        }
        return $traits;
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|null
     */
    public function resolvePropertyFromPropertyReflection(PhpPropertyReflection $phpPropertyReflection)
    {
        $classReflection = $phpPropertyReflection->getDeclaringClass();
        $fileName = $classReflection->getFileName();
        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === []) {
            return null;
        }
        $nativeReflectionProperty = $phpPropertyReflection->getNativeReflection();
        $desiredClassName = $classReflection->getName();
        $desiredPropertyName = $nativeReflectionProperty->getName();
        /** @var Property|null $propertyNode */
        $propertyNode = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use($desiredClassName, $desiredPropertyName, &$propertyNode) : ?int {
            if (!$node instanceof ClassLike) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $desiredClassName)) {
                return null;
            }
            $property = $node->getProperty($desiredPropertyName);
            if ($property instanceof Property) {
                $propertyNode = $property;
                return NodeTraverser::STOP_TRAVERSAL;
            }
            return null;
        });
        if ($propertyNode instanceof Property) {
            return $propertyNode;
        }
        // promoted property
        return $this->findPromotedPropertyByName($nodes, $desiredClassName, $desiredPropertyName);
    }
    /**
     * @return Stmt[]
     */
    public function parseFileNameToDecoratedNodes(?string $fileName) : array
    {
        // probably native PHP → un-parseable
        if ($fileName === null) {
            return [];
        }
        if (isset($this->parsedFileNodes[$fileName])) {
            return $this->parsedFileNodes[$fileName];
        }
        try {
            $stmts = $this->smartPhpParser->parseFile($fileName);
        } catch (Throwable $throwable) {
            /**
             * phpstan.phar contains jetbrains/phpstorm-stubs which the code is not downgraded
             * that if read from lower php < 8.1 may cause crash
             *
             * @see https://github.com/rectorphp/rector/issues/8193 on php 8.0
             * @see https://github.com/rectorphp/rector/issues/8145 on php 7.4
             */
            if (\strpos($fileName, 'phpstan.phar') !== \false) {
                return [];
            }
            throw $throwable;
        }
        return $this->parsedFileNodes[$fileName] = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($fileName, $stmts);
    }
    private function locateClassMethodInTrait(string $methodName, MethodReflection $methodReflection) : ?ClassMethod
    {
        $classReflection = $methodReflection->getDeclaringClass();
        $traits = $this->parseClassReflectionTraits($classReflection);
        /** @var ClassMethod|null $classMethod */
        $classMethod = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($traits, function (Node $node) use($methodName, &$classMethod) : ?int {
            if (!$node instanceof ClassMethod) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $methodName)) {
                return null;
            }
            $classMethod = $node;
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $classMethod;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function findPromotedPropertyByName(array $stmts, string $desiredClassName, string $desiredPropertyName) : ?Param
    {
        /** @var Param|null $paramNode */
        $paramNode = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, function (Node $node) use($desiredClassName, $desiredPropertyName, &$paramNode) {
            if (!$node instanceof Class_) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $desiredClassName)) {
                return null;
            }
            $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
            if (!$constructClassMethod instanceof ClassMethod) {
                return null;
            }
            foreach ($constructClassMethod->getParams() as $param) {
                if ($param->flags === 0) {
                    continue;
                }
                if ($this->nodeNameResolver->isName($param, $desiredPropertyName)) {
                    $paramNode = $param;
                    return NodeTraverser::STOP_TRAVERSAL;
                }
            }
        });
        return $paramNode;
    }
    private function resolveFunctionFromFuncCall(FuncCall $funcCall, Scope $scope) : ?Function_
    {
        if ($funcCall->name instanceof Expr) {
            return null;
        }
        if (!$this->reflectionProvider->hasFunction($funcCall->name, $scope)) {
            return null;
        }
        $functionReflection = $this->reflectionProvider->getFunction($funcCall->name, $scope);
        return $this->resolveFunctionFromFunctionReflection($functionReflection);
    }
}
