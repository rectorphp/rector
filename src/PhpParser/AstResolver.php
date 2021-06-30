<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210630\Symplify\SmartFileSystem\SmartFileSystem;
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
     * @var array<class-string, Class_|null>
     */
    private $classesByName = [];
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
    public function __construct(\PhpParser\Parser $parser, \RectorPrefix20210630\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \PhpParser\NodeFinder $nodeFinder, \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
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
    public function resolveClassFromObjectType(\PHPStan\Type\ObjectType $objectType) : ?\PhpParser\Node\Stmt\Class_
    {
        if (!$this->reflectionProvider->hasClass($objectType->getClassName())) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        return $this->resolveClassFromClassReflection($classReflection, $objectType->getClassName());
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
        $nodes = (array) $this->parser->parse($fileContent);
        $smartFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($fileName);
        $file = new \Rector\Core\ValueObject\Application\File($smartFileInfo, $smartFileInfo->getContents());
        $nodes = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);
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
        $nodes = (array) $this->parser->parse($fileContent);
        $smartFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($fileName);
        $file = new \Rector\Core\ValueObject\Application\File($smartFileInfo, $smartFileInfo->getContents());
        $nodes = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);
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
        return $this->resolveClassMethodFromMethodReflection($methodReflection);
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
            $callerStaticType = $this->nodeTypeResolver->resolve($call->var);
        } else {
            $callerStaticType = $this->nodeTypeResolver->resolve($call->class);
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
    private function resolveClassFromClassReflection(\PHPStan\Reflection\ClassReflection $classReflection, string $className) : ?\PhpParser\Node\Stmt\Class_
    {
        if ($classReflection->isBuiltin()) {
            return null;
        }
        if (isset($this->classesByName[$classReflection->getName()])) {
            return $this->classesByName[$classReflection->getName()];
        }
        $fileName = $classReflection->getFileName();
        // probably internal class
        if ($fileName === \false) {
            // avoid parsing falsy-file again
            $this->classesByName[$classReflection->getName()] = null;
            return null;
        }
        $fileContent = $this->smartFileSystem->readFile($fileName);
        $nodes = $this->parser->parse($fileContent);
        if ($nodes === null) {
            // avoid parsing falsy-file again
            $this->classesByName[$classReflection->getName()] = null;
            return null;
        }
        /** @var Class_[] $classes */
        $classes = $this->betterNodeFinder->findInstanceOf($nodes, \PhpParser\Node\Stmt\Class_::class);
        $reflectionClassName = $classReflection->getName();
        foreach ($classes as $class) {
            if ($reflectionClassName !== $className) {
                continue;
            }
            $this->classesByName[$classReflection->getName()] = $class;
            return $class;
        }
        $this->classesByName[$classReflection->getName()] = null;
        return null;
    }
}
