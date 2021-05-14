<?php

declare (strict_types=1);
namespace Rector\Core\Reflection;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210514\Symplify\SmartFileSystem\SmartFileSystem;
final class FunctionLikeReflectionParser
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
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PhpParser\Parser $parser, \RectorPrefix20210514\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \PhpParser\NodeFinder $nodeFinder, \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->parser = $parser;
        $this->smartFileSystem = $smartFileSystem;
        $this->nodeFinder = $nodeFinder;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function parseMethodReflection(\PHPStan\Reflection\MethodReflection $methodReflection) : ?\PhpParser\Node\Stmt\ClassMethod
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
        $nodes = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes, $smartFileInfo);
        $class = $this->nodeFinder->findFirstInstanceOf($nodes, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        return $class->getMethod($methodReflection->getName());
    }
    /**
     * @param MethodCall|StaticCall|Node $node
     */
    public function parseCaller(\PhpParser\Node $node) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if (!$node instanceof \PhpParser\Node\Expr\MethodCall && !$node instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        /** @var ObjectType|ThisType $objectType */
        $objectType = $node instanceof \PhpParser\Node\Expr\MethodCall ? $this->nodeTypeResolver->resolve($node->var) : $this->nodeTypeResolver->resolve($node->class);
        if ($objectType instanceof \PHPStan\Type\ThisType) {
            $objectType = $objectType->getStaticObjectType();
        }
        $className = $objectType->getClassName();
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $methodName = (string) $this->nodeNameResolver->getName($node->name);
        if (!$classReflection->hasMethod($methodName)) {
            return null;
        }
        if ($classReflection->isBuiltIn()) {
            return null;
        }
        $fileName = $classReflection->getFileName();
        if (!$fileName) {
            return null;
        }
        $fileContent = $this->smartFileSystem->readfile($fileName);
        $nodes = $this->parser->parse($fileContent);
        return $this->getClassMethodFromNodes((array) $nodes, $classReflection, $className, $methodName);
    }
    /**
     * @param Stmt[] $nodes
     */
    private function getClassMethodFromNodes(array $nodes, \PHPStan\Reflection\ClassReflection $classReflection, string $className, string $methodName) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $reflectionClass = $classReflection->getNativeReflection();
        $shortName = $reflectionClass->getShortName();
        foreach ($nodes as $node) {
            if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
                /** @var Stmt[] $nodeStmts */
                $nodeStmts = $node->stmts;
                $classMethod = $this->getClassMethodFromNodes($nodeStmts, $classReflection, $className, $methodName);
                if ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                    return $classMethod;
                }
            }
            $classMethod = $this->getClassMethod($node, $shortName, $methodName);
            if ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return $classMethod;
            }
        }
        return null;
    }
    private function getClassMethod(\PhpParser\Node\Stmt $stmt, string $shortClassName, string $methodName) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if ($stmt instanceof \PhpParser\Node\Stmt\Class_) {
            $name = (string) $this->nodeNameResolver->getName($stmt);
            if ($name === $shortClassName) {
                return $stmt->getMethod($methodName);
            }
        }
        return null;
    }
}
