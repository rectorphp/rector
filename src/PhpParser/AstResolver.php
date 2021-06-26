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
use RectorPrefix20210626\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * The nodes provided by this resolver is for read-only analysis only!
 * They are not part of node tree processed by Rector, so any changes will not make effect in final printed file.
 */
final class AstResolver
{
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
    public function __construct(\PhpParser\Parser $parser, \RectorPrefix20210626\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \PhpParser\NodeFinder $nodeFinder, \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
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
        $fileName = $classReflection->getFileName();
        if ($fileName === \false) {
            return null;
        }
        $fileContent = $this->smartFileSystem->readFile($fileName);
        if (!\is_string($fileContent)) {
            return null;
        }
        $nodes = (array) $this->parser->parse($fileContent);
        $smartFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($fileName);
        $file = new \Rector\Core\ValueObject\Application\File($smartFileInfo, $smartFileInfo->getContents());
        $nodes = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);
        $class = $this->nodeFinder->findFirstInstanceOf($nodes, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        return $class->getMethod($methodReflection->getName());
    }
    public function resolveFunctionFromFunctionReflection(\PHPStan\Reflection\Php\PhpFunctionReflection $phpFunctionReflection) : ?\PhpParser\Node\Stmt\Function_
    {
        $fileName = $phpFunctionReflection->getFileName();
        if ($fileName === \false) {
            return null;
        }
        $fileContent = $this->smartFileSystem->readFile($fileName);
        if (!\is_string($fileContent)) {
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
            return $function;
        }
        return null;
    }
    /**
     * @param class-string $className
     */
    public function resolveClassMethod(string $className, string $methodName) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflection($className, $methodName);
        if ($methodReflection === null) {
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
        /** @var string $fileName */
        $fileName = $classReflection->getFileName();
        /** @var Node[] $contentNodes */
        $contentNodes = $this->parser->parse($this->smartFileSystem->readFile($fileName));
        /** @var Class_[] $classes */
        $classes = $this->betterNodeFinder->findInstanceOf($contentNodes, \PhpParser\Node\Stmt\Class_::class);
        $reflectionClassName = $classReflection->getName();
        foreach ($classes as $class) {
            if ($reflectionClassName === $className) {
                return $class;
            }
        }
        return null;
    }
}
