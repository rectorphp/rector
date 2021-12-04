<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Node\Value;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ConstFetchAnalyzer;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
/**
 * @see \Rector\Core\Tests\PhpParser\Node\Value\ValueResolverTest
 */
final class ValueResolver
{
    /**
     * @var \PhpParser\ConstExprEvaluator|null
     */
    private $constExprEvaluator;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ConstFetchAnalyzer
     */
    private $constFetchAnalyzer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\NodeAnalyzer\ConstFetchAnalyzer $constFetchAnalyzer, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->constFetchAnalyzer = $constFetchAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
        $this->currentFileProvider = $currentFileProvider;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param mixed $value
     */
    public function isValue(\PhpParser\Node\Expr $expr, $value) : bool
    {
        return $this->getValue($expr) === $value;
    }
    public function getStringValue(\PhpParser\Node\Expr $expr) : string
    {
        $resolvedValue = $this->getValue($expr);
        if (!\is_string($resolvedValue)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $resolvedValue;
    }
    /**
     * @return mixed|null
     */
    public function getValue(\PhpParser\Node\Expr $expr, bool $resolvedClassReference = \false)
    {
        if ($expr instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            return $this->processConcat($expr, $resolvedClassReference);
        }
        if ($expr instanceof \PhpParser\Node\Expr\ClassConstFetch && $resolvedClassReference) {
            $class = $this->nodeNameResolver->getName($expr->class);
            if (\in_array($class, [\Rector\Core\Enum\ObjectReference::SELF()->getValue(), \Rector\Core\Enum\ObjectReference::STATIC()->getValue()], \true)) {
                // @todo scope is needed
                $classLike = $this->betterNodeFinder->findParentType($expr, \PhpParser\Node\Stmt\ClassLike::class);
                if ($classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
                    return (string) $this->nodeNameResolver->getName($classLike);
                }
            }
            if ($this->nodeNameResolver->isName($expr->name, 'class')) {
                return $class;
            }
        }
        try {
            $constExprEvaluator = $this->getConstExprEvaluator();
            $value = $constExprEvaluator->evaluateDirectly($expr);
        } catch (\PhpParser\ConstExprEvaluationException $exception) {
            $value = null;
        }
        if ($value !== null) {
            return $value;
        }
        if ($expr instanceof \PhpParser\Node\Expr\ConstFetch) {
            return $this->nodeNameResolver->getName($expr);
        }
        $nodeStaticType = $this->nodeTypeResolver->getType($expr);
        if ($nodeStaticType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return $this->extractConstantArrayTypeValue($nodeStaticType);
        }
        if ($nodeStaticType instanceof \PHPStan\Type\ConstantScalarType) {
            return $nodeStaticType->getValue();
        }
        return null;
    }
    /**
     * @param mixed[] $expectedValues
     */
    public function isValues(\PhpParser\Node\Expr $expr, array $expectedValues) : bool
    {
        foreach ($expectedValues as $expectedValue) {
            if ($this->isValue($expr, $expectedValue)) {
                return \true;
            }
        }
        return \false;
    }
    public function isFalse(\PhpParser\Node $node) : bool
    {
        return $this->constFetchAnalyzer->isFalse($node);
    }
    public function isTrueOrFalse(\PhpParser\Node $node) : bool
    {
        return $this->constFetchAnalyzer->isTrueOrFalse($node);
    }
    public function isTrue(\PhpParser\Node $node) : bool
    {
        return $this->constFetchAnalyzer->isTrue($node);
    }
    public function isNull(\PhpParser\Node $node) : bool
    {
        return $this->constFetchAnalyzer->isNull($node);
    }
    public function isValueEqual(\PhpParser\Node\Expr $firstExpr, \PhpParser\Node\Expr $secondExpr) : bool
    {
        $firstValue = $this->getValue($firstExpr);
        $secondValue = $this->getValue($secondExpr);
        return $firstValue === $secondValue;
    }
    /**
     * @param Expr[]|null[] $nodes
     * @param mixed[] $expectedValues
     */
    public function areValuesEqual(array $nodes, array $expectedValues) : bool
    {
        foreach ($nodes as $i => $node) {
            if ($node === null) {
                return \false;
            }
            if (!$this->isValue($node, $expectedValues[$i])) {
                return \false;
            }
        }
        return \true;
    }
    private function processConcat(\PhpParser\Node\Expr\BinaryOp\Concat $concat, bool $resolvedClassReference) : string
    {
        return $this->getValue($concat->left, $resolvedClassReference) . $this->getValue($concat->right, $resolvedClassReference);
    }
    private function getConstExprEvaluator() : \PhpParser\ConstExprEvaluator
    {
        if ($this->constExprEvaluator !== null) {
            return $this->constExprEvaluator;
        }
        $this->constExprEvaluator = new \PhpParser\ConstExprEvaluator(function (\PhpParser\Node\Expr $expr) {
            if ($expr instanceof \PhpParser\Node\Scalar\MagicConst\Dir) {
                // __DIR__
                return $this->resolveDirConstant();
            }
            if ($expr instanceof \PhpParser\Node\Scalar\MagicConst\File) {
                // __FILE__
                return $this->resolveFileConstant($expr);
            }
            // resolve "SomeClass::SOME_CONST"
            if ($expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                return $this->resolveClassConstFetch($expr);
            }
            throw new \PhpParser\ConstExprEvaluationException(\sprintf('Expression of type "%s" cannot be evaluated', $expr->getType()));
        });
        return $this->constExprEvaluator;
    }
    /**
     * @return mixed[]
     */
    private function extractConstantArrayTypeValue(\PHPStan\Type\Constant\ConstantArrayType $constantArrayType) : ?array
    {
        $keys = [];
        foreach ($constantArrayType->getKeyTypes() as $i => $keyType) {
            /** @var ConstantScalarType $keyType */
            $keys[$i] = $keyType->getValue();
        }
        $values = [];
        foreach ($constantArrayType->getValueTypes() as $i => $valueType) {
            if ($valueType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
                $value = $this->extractConstantArrayTypeValue($valueType);
            } elseif ($valueType instanceof \PHPStan\Type\ConstantScalarType) {
                $value = $valueType->getValue();
            } elseif ($valueType instanceof \PHPStan\Type\TypeWithClassName) {
                continue;
            } else {
                return null;
            }
            $values[$keys[$i]] = $value;
        }
        return $values;
    }
    private function resolveDirConstant() : string
    {
        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->getPath();
    }
    private function resolveFileConstant(\PhpParser\Node\Scalar\MagicConst\File $file) : string
    {
        $file = $this->currentFileProvider->getFile();
        $smartFileInfo = $file->getSmartFileInfo();
        return $smartFileInfo->getPathname();
    }
    /**
     * @return string|mixed
     */
    private function resolveClassConstFetch(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch)
    {
        $class = $this->nodeNameResolver->getName($classConstFetch->class);
        $constant = $this->nodeNameResolver->getName($classConstFetch->name);
        if ($class === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($constant === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($class === \Rector\Core\Enum\ObjectReference::SELF()->getValue()) {
            $classLike = $this->betterNodeFinder->findParentType($classConstFetch, \PhpParser\Node\Stmt\ClassLike::class);
            if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $class = (string) $this->nodeNameResolver->getName($classLike);
        }
        if ($constant === 'class') {
            return $class;
        }
        $classConstantReference = $class . '::' . $constant;
        if (\defined($classConstantReference)) {
            return \constant($classConstantReference);
        }
        if ($this->reflectionProvider->hasClass($class)) {
            $classReflection = $this->reflectionProvider->getClass($class);
            if ($classReflection->hasConstant($constant)) {
                $constantReflection = $classReflection->getConstant($constant);
                return $constantReflection->getValue();
            }
        }
        // fallback to constant reference itself, to avoid fatal error
        return $classConstantReference;
    }
}
