<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser;

use RectorPrefix202303\Nette\Utils\FileSystem;
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
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpDocParser\PhpParser\SmartPhpParser;
/**
 * The nodes provided by this resolver is for read-only analysis only!
 * They are not part of node tree processed by Rector, so any changes will not make effect in final printed file.
 */
final class AstResolver
{
    /**
     * Parsing files is very heavy performance, so this will help to leverage it
     * The value can be also null, when no statements could be parsed from the file.
     *
     * @var array<string, Stmt[]|null>
     */
    private $parsedFileNodes = [];
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
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
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
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\ClassLikeAstResolver
     */
    private $classLikeAstResolver;
    public function __construct(SmartPhpParser $smartPhpParser, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver, NodeTypeResolver $nodeTypeResolver, \Rector\Core\PhpParser\ClassLikeAstResolver $classLikeAstResolver)
    {
        $this->smartPhpParser = $smartPhpParser;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->classLikeAstResolver = $classLikeAstResolver;
    }
    /**
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
        $classLikeName = $classReflection->getName();
        $methodName = $methodReflection->getName();
        $fileName = $classReflection->getFileName();
        // probably native PHP method → un-parseable
        if ($fileName === null) {
            return null;
        }
        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === null) {
            return null;
        }
        /** @var ClassMethod|null $classMethod */
        $classMethod = $this->betterNodeFinder->findFirst($nodes, function (Node $node) use($classLikeName, $methodName) : bool {
            if (!$node instanceof ClassMethod) {
                return \false;
            }
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof ClassLike) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($parentNode, $classLikeName)) {
                return \false;
            }
            return $parentNode->getMethod($methodName) === $node;
        });
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
        $functionName = $functionReflection->getName();
        $fileName = $functionReflection->getFileName();
        if ($fileName === null) {
            return null;
        }
        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === null) {
            return null;
        }
        /** @var Function_|null $function */
        $function = $this->betterNodeFinder->findFirst($nodes, function (Node $node) use($functionName) : bool {
            return $node instanceof Function_ && $this->nodeNameResolver->isName($node, $functionName);
        });
        return $function;
    }
    /**
     * @param class-string $className
     */
    public function resolveClassMethod(string $className, string $methodName) : ?ClassMethod
    {
        $methodReflection = $this->reflectionResolver->resolveMethodReflection($className, $methodName, null);
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
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    public function resolveClassMethodFromCall($call) : ?ClassMethod
    {
        if ($call instanceof MethodCall) {
            $callerStaticType = $this->nodeTypeResolver->getType($call->var);
        } else {
            $callerStaticType = $this->nodeTypeResolver->getType($call->class);
        }
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
        return $this->classLikeAstResolver->resolveClassFromClassReflection($classReflection);
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
            if ($fileName === null) {
                continue;
            }
            $nodes = $this->parseFileNameToDecoratedNodes($fileName);
            if ($nodes === null) {
                continue;
            }
            /** @var Trait_|null $trait */
            $trait = $this->betterNodeFinder->findFirst($nodes, function (Node $node) use($classLike) : bool {
                return $node instanceof Trait_ && $this->nodeNameResolver->isName($node, $classLike->getName());
            });
            if (!$trait instanceof Trait_) {
                continue;
            }
            $traits[] = $trait;
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
        if ($fileName === null) {
            return null;
        }
        $nodes = $this->parseFileNameToDecoratedNodes($fileName);
        if ($nodes === null) {
            return null;
        }
        $nativeReflectionProperty = $phpPropertyReflection->getNativeReflection();
        $desiredClassName = $classReflection->getName();
        $desiredPropertyName = $nativeReflectionProperty->getName();
        /** @var Property|null $property */
        $property = $this->betterNodeFinder->findFirst($nodes, function (Node $node) use($desiredClassName, $desiredPropertyName) : bool {
            if (!$node instanceof Property) {
                return \false;
            }
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if (!$parentNode instanceof ClassLike) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($parentNode, $desiredClassName)) {
                return \false;
            }
            return $parentNode->getProperty($desiredPropertyName) === $node;
        });
        if ($property instanceof Property) {
            return $property;
        }
        // promoted property
        return $this->findPromotedPropertyByName($nodes, $desiredPropertyName);
    }
    private function locateClassMethodInTrait(string $methodName, MethodReflection $methodReflection) : ?ClassMethod
    {
        $classReflection = $methodReflection->getDeclaringClass();
        $traits = $this->parseClassReflectionTraits($classReflection);
        /** @var ClassMethod|null $classMethod */
        $classMethod = $this->betterNodeFinder->findFirst($traits, function (Node $node) use($methodName) : bool {
            return $node instanceof ClassMethod && $this->nodeNameResolver->isName($node, $methodName);
        });
        if ($classMethod instanceof ClassMethod) {
            return $classMethod;
        }
        return null;
    }
    /**
     * @return Stmt[]|null
     */
    private function parseFileNameToDecoratedNodes(string $fileName) : ?array
    {
        if (isset($this->parsedFileNodes[$fileName])) {
            return $this->parsedFileNodes[$fileName];
        }
        $stmts = $this->smartPhpParser->parseFile($fileName);
        if ($stmts === []) {
            return $this->parsedFileNodes[$fileName] = null;
        }
        $file = new File($fileName, FileSystem::read($fileName));
        return $this->parsedFileNodes[$fileName] = $this->nodeScopeAndMetadataDecorator->decorateNodesFromFile($file, $stmts);
    }
    /**
     * @param Stmt[] $stmts
     */
    private function findPromotedPropertyByName(array $stmts, string $desiredPropertyName) : ?Param
    {
        $class = $this->betterNodeFinder->findFirstInstanceOf($stmts, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
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
