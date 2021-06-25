<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use PhpParser\Node;
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
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ReflectionAstResolver
{
    public function __construct(
        private Parser $parser,
        private SmartFileSystem $smartFileSystem,
        private NodeFinder $nodeFinder,
        private NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function resolveObjectType(ObjectType $objectType): ?Class_
    {
        if (! $this->reflectionProvider->hasClass($objectType->getClassName())) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        return $this->resolveClassReflection($classReflection, $objectType->getClassName());
    }

    public function resolveMethodReflection(MethodReflection $methodReflection): ?ClassMethod
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

    private function resolveClassReflection(ClassReflection $classReflection, string $className): ?Class_
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
        if ($classes === []) {
            return null;
        }

        $reflectionClassName = $classReflection->getName();
        foreach ($classes as $class) {
            if ($reflectionClassName === $className) {
                return $class;
            }
        }

        return null;
    }
}
