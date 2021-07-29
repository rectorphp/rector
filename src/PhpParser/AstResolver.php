<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser;

use PhpParser\Node;
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
use Symplify\SmartFileSystem\SmartFileSystem;

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
    private array $classMethodsByClassAndMethod = [];

    /**
     * Parsing files is very heavy performance, so this will help to leverage it
     * The value can be also null, as the method might not exist in the class.
     *
     * @var array<string, Function_|null>>
     */
    private array $functionsByName = [];

    /**
     * Parsing files is very heavy performance, so this will help to leverage it
     * The value can be also null, as the method might not exist in the class.
     *
     * @var array<class-string, Class_|Trait_|Interface_|null>
     */
    private array $classLikesByName = [];

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

    public function resolveClassFromName(string $className): Class_ | Trait_ | Interface_ | null
    {
        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        return $this->resolveClassFromClassReflection($classReflection, $className);
    }

    public function resolveClassFromObjectType(
        TypeWithClassName $typeWithClassName
    ): Class_ | Trait_ | Interface_ | null {
        return $this->resolveClassFromName($typeWithClassName->getClassName());
    }

    public function resolveClassMethodFromMethodReflection(MethodReflection $methodReflection): ?ClassMethod
    {
        $classReflection = $methodReflection->getDeclaringClass();

        if (isset($this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()])) {
            return $this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()];
        }

        $fileName = $classReflection->getFileName();

        // probably native PHP method → un-parseable
        if ($fileName === false) {
            return null;
        }

        $fileContent = $this->smartFileSystem->readFile($fileName);
        if (! is_string($fileContent)) {
            // avoids parsing again falsy file
            $this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()] = null;
            return null;
        }

        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === null) {
            return null;
        }

        $class = $this->nodeFinder->findFirstInstanceOf($nodes, Class_::class);
        if (! $class instanceof Class_) {
            // avoids looking for a class in a file where is not present
            $this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()] = null;
            return null;
        }

        $classMethod = $class->getMethod($methodReflection->getName());
        $this->classMethodsByClassAndMethod[$classReflection->getName()][$methodReflection->getName()] = $classMethod;

        return $classMethod;
    }

    public function resolveFunctionFromFunctionReflection(PhpFunctionReflection $phpFunctionReflection): ?Function_
    {
        if (isset($this->functionsByName[$phpFunctionReflection->getName()])) {
            return $this->functionsByName[$phpFunctionReflection->getName()];
        }

        $fileName = $phpFunctionReflection->getFileName();
        if ($fileName === false) {
            return null;
        }

        $fileContent = $this->smartFileSystem->readFile($fileName);
        if (! is_string($fileContent)) {
            // to avoid parsing missing function again
            $this->functionsByName[$phpFunctionReflection->getName()] = null;
            return null;
        }

        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === null) {
            return null;
        }

        /** @var Function_[] $functions */
        $functions = $this->nodeFinder->findInstanceOf($nodes, Function_::class);
        foreach ($functions as $function) {
            if (! $this->nodeNameResolver->isName($function, $phpFunctionReflection->getName())) {
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
    public function resolveClassMethod(string $className, string $methodName): ?ClassMethod
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflection($className, $methodName, null);
        if (! $methodReflection instanceof MethodReflection) {
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

    public function resolveClassFromClassReflection(
        ClassReflection $classReflection,
        string $className
    ): Trait_ | Class_ | Interface_ | null {
        if ($classReflection->isBuiltin()) {
            return null;
        }

        if (isset($this->classLikesByName[$classReflection->getName()])) {
            return $this->classLikesByName[$classReflection->getName()];
        }

        $fileName = $classReflection->getFileName();

        // probably internal class
        if ($fileName === false) {
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
        $classLikes = $this->betterNodeFinder->findInstanceOf($nodes, ClassLike::class);

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
    public function parseClassReflectionTraits(ClassReflection $classReflection): array
    {
        $classLikes = $classReflection->getTraits(true);
        $traits = [];
        foreach ($classLikes as $classLike) {
            $fileName = $classLike->getFileName();
            if (! $fileName) {
                continue;
            }

            $nodes = $this->parseFileNameToDecoratedNodes($fileName);
            if ($nodes === null) {
                continue;
            }

            /** @var Trait_|null $trait */
            $trait = $this->betterNodeFinder->findFirst(
                $nodes,
                fn (Node $node): bool => $node instanceof Trait_ && $this->nodeNameResolver->isName(
                    $node,
                    $classLike->getName()
                )
            );

            if (! $trait instanceof Trait_) {
                continue;
            }

            $traits[] = $trait;
        }

        return $traits;
    }

    public function resolvePropertyFromPropertyReflection(
        ReflectionProperty $reflectionProperty
    ): Property | Param | null {
        $reflectionClass = $reflectionProperty->getDeclaringClass();

        $fileName = $reflectionClass->getFileName();
        if ($fileName === false) {
            return null;
        }

        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === null) {
            return null;
        }

        $desiredPropertyName = $reflectionProperty->name;

        /** @var Property[] $properties */
        $properties = $this->betterNodeFinder->findInstanceOf($nodes, Property::class);
        foreach ($properties as $property) {
            if ($this->nodeNameResolver->isName($property, $desiredPropertyName)) {
                return $property;
            }
        }

        // promoted property
        return $this->findPromotedPropertyByName($nodes, $desiredPropertyName);
    }

    /**
     * @return Stmt[]|null
     */
    private function parseFileNameToDecoratedNodes(string $fileName): ?array
    {
        $fileContent = $this->smartFileSystem->readFile($fileName);
        $nodes = $this->parser->parse($fileContent);
        if ($nodes === null) {
            return null;
        }

        $smartFileInfo = new SmartFileInfo($fileName);
        $file = new File($smartFileInfo, $smartFileInfo->getContents());

        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $nodes);
    }

    /**
     * @param Stmt[] $nodes
     */
    private function findPromotedPropertyByName(array $nodes, string $desiredPropertyName): ?Param
    {
        $class = $this->betterNodeFinder->findFirstInstanceOf($nodes, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
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
}
