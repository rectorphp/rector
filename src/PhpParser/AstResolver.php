<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionProperty;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * The nodes provided by this resolver is for read-only analysis only!
 * They are not part of node tree processed by Rector, so any changes will not make effect in final printed file.
 */
final class AstResolver
{
    /**
     * Parsing files is very heavy performance, so this will help to leverage it
     * The value can be also null, as the method might not exist in the class.
     *
     * @var array<class-string, array<string, ClassMethod|null>>
     */
    private $classMethodsByClassAndMethod = [];
    /**
     * Parsing files is very heavy performance, so this will help to leverage it
     * The value can be also null, as the method might not exist in the class.
     *
     * @var array<string, Function_|null>>
     */
    private $functionsByName = [];
    /**
     * Parsing files is very heavy performance, so this will help to leverage it
     * The value can be also null, as the method might not exist in the class.
     *
     * @var array<class-string, Class_|Trait_|Interface_|null>
     */
    private $classLikesByName = [];
    /**
     * @var \PhpParser\Parser
     */
    private $parser;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \PhpParser\NodeFinder
     */
    private $nodeFinder;
    /**
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\PhpParser\Parser $parser, \RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \PhpParser\NodeFinder $nodeFinder, \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->parser = $parser;
        $this->smartFileSystem = $smartFileSystem;
        $this->nodeFinder = $nodeFinder;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_|\PhpParser\Node\Stmt\Interface_|null
     */
    public function resolveClassFromName(string $className)
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        return $this->resolveClassFromClassReflection($classReflection, $className);
    }
    /**
     * @return \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Trait_|\PhpParser\Node\Stmt\Interface_|null
     */
    public function resolveClassFromObjectType(\PHPStan\Type\TypeWithClassName $typeWithClassName)
    {
        return $this->resolveClassFromName($typeWithClassName->getClassName());
    }
    public function resolveClassMethodFromMethodReflection(\PHPStan\Reflection\MethodReflection $methodReflection) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $classReflection = $methodReflection->getDeclaringClass();
        if (isset($this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()])) {
            return $this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()];
        }
        $fileName = $classReflection->getFileName();
        // probably native PHP method → un-parseable
        if ($fileName === \false) {
            return null;
        }
        $fileContent = $this->smartFileSystem->readFile($fileName);
        if (!\is_string($fileContent)) {
            // avoids parsing again falsy file
            $this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()] = null;
            return null;
        }
        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === null) {
            return null;
        }
        $class = $this->nodeFinder->findFirstInstanceOf($nodes, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            // avoids looking for a class in a file where is not present
            $this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()] = null;
            return null;
        }
        $classMethod = $class->getMethod($methodReflection->getName());
        $this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()] = $classMethod;
        return $classMethod;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|null
     */
    public function resolveClassMethodOrFunctionFromCall($call, \PHPStan\Analyser\Scope $scope)
    {
        if ($call instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->resolveFunctionFromFuncCall($call, $scope);
        }
        return $this->resolveClassMethodFromCall($call);
    }
    public function resolveFunctionFromFunctionReflection(\PHPStan\Reflection\Php\PhpFunctionReflection $phpFunctionReflection) : ?\PhpParser\Node\Stmt\Function_
    {
        if (isset($this->functionsByName[$phpFunctionReflection->getName()])) {
            return $this->functionsByName[$phpFunctionReflection->getName()];
        }
        $fileName = $phpFunctionReflection->getFileName();
        if ($fileName === \false) {
            return null;
        }
        $fileContent = $this->smartFileSystem->readFile($fileName);
        if (!\is_string($fileContent)) {
            // to avoid parsing missing function again
            $this->functionsByName[$phpFunctionReflection->getName()] = null;
            return null;
        }
        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === null) {
            return null;
        }
        /** @var Function_[] $functions */
        $functions = $this->nodeFinder->findInstanceOf($nodes, \PhpParser\Node\Stmt\Function_::class);
        foreach ($functions as $function) {
            if (!$this->nodeNameResolver->isName($function, $phpFunctionReflection->getName())) {
                continue;
            }
            // to avoid parsing missing function again
            $this->functionsByName[$phpFunctionReflection->getName()] = $function;
            return $function;
        }
        // to avoid parsing missing function again
        $this->functionsByName[$phpFunctionReflection->getName()] = null;
        return null;
    }
    /**
     * @param class-string $className
     */
    public function resolveClassMethod(string $className, string $methodName) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflection($className, $methodName, null);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        $classMethod = $this->resolveClassMethodFromMethodReflection($methodReflection);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->locateClassMethodInTrait($methodName, $methodReflection);
        }
        return $classMethod;
    }
    public function resolveClassMethodFromMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        return $this->resolveClassMethodFromCall($methodCall);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    public function resolveClassMethodFromCall($call) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if ($call instanceof \PhpParser\Node\Expr\MethodCall) {
            $callerStaticType = $this->nodeTypeResolver->getType($call->var);
        } else {
            $callerStaticType = $this->nodeTypeResolver->getType($call->class);
        }
        if (!$callerStaticType instanceof \PHPStan\Type\TypeWithClassName) {
            return null;
        }
        $methodName = $this->nodeNameResolver->getName($call->name);
        if ($methodName === null) {
            return null;
        }
        return $this->resolveClassMethod($callerStaticType->getClassName(), $methodName);
    }
    /**
     * @return \PhpParser\Node\Stmt\Trait_|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|null
     */
    public function resolveClassFromClassReflection(\PHPStan\Reflection\ClassReflection $classReflection, string $className)
    {
        if ($classReflection->isBuiltin()) {
            return null;
        }
        if (isset($this->classLikesByName[$classReflection->getName()])) {
            return $this->classLikesByName[$classReflection->getName()];
        }
        $fileName = $classReflection->getFileName();
        // probably internal class
        if ($fileName === \false) {
            // avoid parsing falsy-file again
            $this->classLikesByName[$classReflection->getName()] = null;
            return null;
        }
        $fileContent = $this->smartFileSystem->readFile($fileName);
        $nodes = $this->parser->parse($fileContent);
        if ($nodes === null) {
            // avoid parsing falsy-file again
            $this->classLikesByName[$classReflection->getName()] = null;
            return null;
        }
        /** @var array<Class_|Trait_|Interface_> $classLikes */
        $classLikes = $this->betterNodeFinder->findInstanceOf($nodes, \PhpParser\Node\Stmt\ClassLike::class);
        $reflectionClassName = $classReflection->getName();
        foreach ($classLikes as $classLike) {
            if ($reflectionClassName !== $className) {
                continue;
            }
            $this->classLikesByName[$classReflection->getName()] = $classLike;
            return $classLike;
        }
        $this->classLikesByName[$classReflection->getName()] = null;
        return null;
    }
    /**
     * @return Trait_[]
     */
    public function parseClassReflectionTraits(\PHPStan\Reflection\ClassReflection $classReflection) : array
    {
        $classLikes = $classReflection->getTraits(\true);
        $traits = [];
        foreach ($classLikes as $classLike) {
            $fileName = $classLike->getFileName();
            if (!$fileName) {
                continue;
            }
            $nodes = $this->parseFileNameToDecoratedNodes($fileName);
            if ($nodes === null) {
                continue;
            }
            /** @var Trait_|null $trait */
            $trait = $this->betterNodeFinder->findFirst($nodes, function (\PhpParser\Node $node) use($classLike) : bool {
                return $node instanceof \PhpParser\Node\Stmt\Trait_ && $this->nodeNameResolver->isName($node, $classLike->getName());
            });
            if (!$trait instanceof \PhpParser\Node\Stmt\Trait_) {
                continue;
            }
            $traits[] = $trait;
        }
        return $traits;
    }
    /**
     * @return \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|null
     */
    public function resolvePropertyFromPropertyReflection(\ReflectionProperty $reflectionProperty)
    {
        $reflectionClass = $reflectionProperty->getDeclaringClass();
        $fileName = $reflectionClass->getFileName();
        if ($fileName === \false) {
            return null;
        }
        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === null) {
            return null;
        }
        $desiredPropertyName = $reflectionProperty->name;
        /** @var Property[] $properties */
        $properties = $this->betterNodeFinder->findInstanceOf($nodes, \PhpParser\Node\Stmt\Property::class);
        foreach ($properties as $property) {
            if ($this->nodeNameResolver->isName($property, $desiredPropertyName)) {
                return $property;
            }
        }
        // promoted property
        return $this->findPromotedPropertyByName($nodes, $desiredPropertyName);
    }
    private function locateClassMethodInTrait(string $methodName, \PHPStan\Reflection\MethodReflection $methodReflection) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $classReflection = $methodReflection->getDeclaringClass();
        $traits = $this->parseClassReflectionTraits($classReflection);
        /** @var ClassMethod|null $classMethod */
        $classMethod = $this->betterNodeFinder->findFirst($traits, function (\PhpParser\Node $node) use($methodName) : bool {
            return $node instanceof \PhpParser\Node\Stmt\ClassMethod && $this->nodeNameResolver->isName($node, $methodName);
        });
        $this->classMethodsByClassAndMethod[$classReflection->getName()][$methodName] = $classMethod;
        return $classMethod;
    }
    /**
     * @return Stmt[]|null
     */
    private function parseFileNameToDecoratedNodes(string $fileName) : ?array
    {
        $fileContent = $this->smartFileSystem->readFile($fileName);
        $nodes = $this->parser->parse($fileContent);
        if ($nodes === null) {
            return null;
        }
        $smartFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($fileName);
        $file = new \Rector\Core\ValueObject\Application\File($smartFileInfo, $smartFileInfo->getContents());
        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);
    }
    /**
     * @param Stmt[] $nodes
     */
    private function findPromotedPropertyByName(array $nodes, string $desiredPropertyName) : ?\PhpParser\Node\Param
    {
        $class = $this->betterNodeFinder->findFirstInstanceOf($nodes, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        foreach ($constructClassMethod->getParams() as $param) {
            if ($param->flags === 0) {
                continue;
            }
            if ($this->nodeNameResolver->isName($param, $desiredPropertyName)) {
                return $param;
            }
        }
        return null;
    }
    private function resolveFunctionFromFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall, \PHPStan\Analyser\Scope $scope) : ?\PhpParser\Node\Stmt\Function_
    {
        if ($funcCall->name instanceof \PhpParser\Node\Expr) {
            return null;
        }
        if (!$this->reflectionProvider->hasFunction($funcCall->name, $scope)) {
            return null;
        }
        $reflectionFunction = $this->reflectionProvider->getFunction($funcCall->name, $scope);
        if (!$reflectionFunction instanceof \PHPStan\Reflection\Php\PhpFunctionReflection) {
            return null;
        }
        return $this->resolveFunctionFromFunctionReflection($reflectionFunction);
    }
}
