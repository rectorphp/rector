<?php

declare(strict_types=1);

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
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * The nodes provided by this resolver is for read-only analysis only!
 * They are not part of node tree processed by Rector, so any changes will not make effect in final printed file.
 */
final class AstResolver
{
    public function __construct(
        private Parser $parser,
        private SmartFileSystem $smartFileSystem,
        private NodeFinder $nodeFinder,
        private NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver,
        private ReflectionProvider $reflectionProvider,
        private ReflectionResolver $reflectionResolver,
        private NodeTypeResolver $nodeTypeResolver,
    ) {
    }

    public function resolveClassFromObjectType(ObjectType $objectType): ?Class_
    {
        if (! $this->reflectionProvider->hasClass($objectType->getClassName())) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        return $this->resolveClassFromClassReflection($classReflection, $objectType->getClassName());
    }

    public function resolveClassMethodFromMethodReflection(MethodReflection $methodReflection): ?ClassMethod
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

    public function resolveFunctionFromFunctionReflection(PhpFunctionReflection $phpFunctionReflection): ?Function_
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
     * @param class-string $className
     */
    public function resolveClassMethod(string $className, string $methodName): ?ClassMethod
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflection($className, $methodName);
        if ($methodReflection === null) {
            return null;
        }

        return $this->resolveClassMethodFromMethodReflection($methodReflection);
    }

    public function resolveClassMethodFromMethodCall(MethodCall $methodCall): ?ClassMethod
    {
        return $this->resolveClassMethodFromCall($methodCall);
    }

    public function resolveClassMethodFromCall(MethodCall | StaticCall $call): ?ClassMethod
    {
        if ($call instanceof MethodCall) {
            $callerStaticType = $this->nodeTypeResolver->resolve($call->var);
        } else {
            $callerStaticType = $this->nodeTypeResolver->resolve($call->class);
        }

        if (! $callerStaticType instanceof TypeWithClassName) {
            return null;
        }

        $methodName = $this->nodeNameResolver->getName($call->name);
        if ($methodName === null) {
            return null;
        }

        return $this->resolveClassMethod($callerStaticType->getClassName(), $methodName);
    }

    private function resolveClassFromClassReflection(ClassReflection $classReflection, string $className): ?Class_
    {
        if ($classReflection->isBuiltin()) {
            return null;
        }

        /** @var string $fileName */
        $fileName = $classReflection->getFileName();

        /** @var Node[] $contentNodes */
        $contentNodes = $this->parser->parse($this->smartFileSystem->readFile($fileName));

        /** @var Class_[] $classes */
        $classes = $this->betterNodeFinder->findInstanceOf($contentNodes, Class_::class);

        $reflectionClassName = $classReflection->getName();
        foreach ($classes as $class) {
            if ($reflectionClassName === $className) {
                return $class;
            }
        }

        return null;
    }
}
