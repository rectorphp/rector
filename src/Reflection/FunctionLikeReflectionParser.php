<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FunctionLikeReflectionParser
{
    public function __construct(
        private Parser $parser,
        private SmartFileSystem $smartFileSystem,
        private NodeFinder $nodeFinder,
        private NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        private NodeTypeResolver $nodeTypeResolver,
        private NodeNameResolver $nodeNameResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function parseMethodReflection(MethodReflection $methodReflection): ?ClassMethod
    {
        $classReflection = $methodReflection->getDeclaringClass();

        $fileName = $classReflection->getFileName();
        if ($fileName === false) {
            return null;
        }

        $fileContent = $this->smartFileSystem->readFile($fileName);
        if (! is_string($fileContent)) {
            return null;
        }

        $nodes = (array) $this->parser->parse($fileContent);

        $smartFileInfo = new SmartFileInfo($fileName);
        $file = new File($smartFileInfo, $smartFileInfo->getContents());

        $nodes = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);

        $class = $this->nodeFinder->findFirstInstanceOf($nodes, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        return $class->getMethod($methodReflection->getName());
    }

    public function parseFunctionReflection(PhpFunctionReflection $phpFunctionReflection): ?Function_
    {
        $fileName = $phpFunctionReflection->getFileName();
        if ($fileName === false) {
            return null;
        }

        $fileContent = $this->smartFileSystem->readFile($fileName);
        if (! is_string($fileContent)) {
            return null;
        }

        $nodes = (array) $this->parser->parse($fileContent);

        $smartFileInfo = new SmartFileInfo($fileName);
        $file = new File($smartFileInfo, $smartFileInfo->getContents());

        $nodes = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);

        /** @var Function_[] $functions */
        $functions = $this->nodeFinder->findInstanceOf($nodes, Function_::class);
        foreach ($functions as $function) {
            if (! $this->nodeNameResolver->isName($function, $phpFunctionReflection->getName())) {
                continue;
            }

            return $function;
        }

        return null;
    }

    /**
     * @param MethodCall|StaticCall|Node $node
     */
    public function parseCaller(Node $node): ?ClassMethod
    {
        if (! $node instanceof MethodCall && ! $node instanceof StaticCall) {
            return null;
        }

        /** @var ObjectType|ThisType $objectType */
        $objectType = $node instanceof MethodCall
            ? $this->nodeTypeResolver->resolve($node->var)
            : $this->nodeTypeResolver->resolve($node->class);

        if ($objectType instanceof ThisType) {
            $objectType = $objectType->getStaticObjectType();
        }

        $className = $objectType->getClassName();
        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        $methodName = (string) $this->nodeNameResolver->getName($node->name);
        if (! $classReflection->hasMethod($methodName)) {
            return null;
        }

        if ($classReflection->isBuiltIn()) {
            return null;
        }

        $fileName = $classReflection->getFileName();
        if (! $fileName) {
            return null;
        }

        $fileContent = $this->smartFileSystem->readfile($fileName);
        $nodes = $this->parser->parse($fileContent);
        return $this->getClassMethodFromNodes((array) $nodes, $classReflection, $className, $methodName);
    }

    /**
     * @param Stmt[] $nodes
     */
    private function getClassMethodFromNodes(
        array $nodes,
        ClassReflection $classReflection,
        string $className,
        string $methodName
    ): ?ClassMethod {
        $reflectionClass = $classReflection->getNativeReflection();
        $shortName = $reflectionClass->getShortName();

        foreach ($nodes as $node) {
            if ($node instanceof Namespace_) {
                /** @var Stmt[] $nodeStmts */
                $nodeStmts = $node->stmts;
                $classMethod = $this->getClassMethodFromNodes($nodeStmts, $classReflection, $className, $methodName);

                if ($classMethod instanceof ClassMethod) {
                    return $classMethod;
                }
            }

            $classMethod = $this->getClassMethod($node, $shortName, $methodName);
            if ($classMethod instanceof ClassMethod) {
                return $classMethod;
            }
        }

        return null;
    }

    private function getClassMethod(Stmt $stmt, string $shortClassName, string $methodName): ?ClassMethod
    {
        if ($stmt instanceof Class_) {
            $name = (string) $this->nodeNameResolver->getName($stmt);
            if ($name === $shortClassName) {
                return $stmt->getMethod($methodName);
            }
        }

        return null;
    }
}
