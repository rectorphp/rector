<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Value;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ConstantScalarType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

/**
 * @see \Rector\Tests\PhpParser\Node\Value\ValueResolverTest
 */
final class ValueResolver
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        NameResolver $nameResolver,
        NodeTypeResolver $nodeTypeResolver,
        ParsedNodesByType $parsedNodesByType
    ) {
        $this->nameResolver = $nameResolver;
        $this->parsedNodesByType = $parsedNodesByType;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return mixed|null
     */
    public function getValue(Expr $expr)
    {
        try {
            $value = $this->getConstExprEvaluator()->evaluateDirectly($expr);
        } catch (ConstExprEvaluationException $constExprEvaluationException) {
            $value = null;
        }

        if ($value !== null) {
            return $value;
        }

        if ($expr instanceof ConstFetch) {
            return $this->nameResolver->getName($expr);
        }

        $nodeStaticType = $this->nodeTypeResolver->getStaticType($expr);

        if ($nodeStaticType instanceof ConstantArrayType) {
            return $this->extractConstantArrayTypeValue($nodeStaticType);
        }

        if ($nodeStaticType instanceof ConstantScalarType) {
            return $nodeStaticType->getValue();
        }

        return null;
    }

    private function getConstExprEvaluator(): ConstExprEvaluator
    {
        if ($this->constExprEvaluator !== null) {
            return $this->constExprEvaluator;
        }

        $this->constExprEvaluator = new ConstExprEvaluator(function (Expr $expr) {
            if ($expr instanceof Dir) {
                // __DIR__
                return $this->resolveDirConstant($expr);
            }

            if ($expr instanceof File) {
                // __FILE__
                return $this->resolveFileConstant($expr);
            }

            // resolve "SomeClass::SOME_CONST"
            if ($expr instanceof ClassConstFetch) {
                return $this->resolveClassConstFetch($expr);
            }

            throw new ConstExprEvaluationException("Expression of type {$expr->getType()} cannot be evaluated");
        });

        return $this->constExprEvaluator;
    }

    private function resolveDirConstant(Dir $dir): string
    {
        $fileInfo = $dir->getAttribute(AttributeKey::FILE_INFO);
        if (! $fileInfo instanceof SmartFileInfo) {
            throw new ShouldNotHappenException();
        }

        return $fileInfo->getPath();
    }

    private function resolveFileConstant(File $file): string
    {
        $fileInfo = $file->getAttribute(AttributeKey::FILE_INFO);
        if (! $fileInfo instanceof SmartFileInfo) {
            throw new ShouldNotHappenException();
        }

        return $fileInfo->getPathname();
    }

    /**
     * @return mixed
     */
    private function resolveClassConstFetch(ClassConstFetch $classConstFetch)
    {
        $class = $this->nameResolver->getName($classConstFetch->class);
        $constant = $this->nameResolver->getName($classConstFetch->name);

        if ($class === null) {
            throw new ShouldNotHappenException();
        }

        if ($constant === null) {
            throw new ShouldNotHappenException();
        }

        if ($class === 'self') {
            $class = (string) $classConstFetch->class->getAttribute(AttributeKey::CLASS_NAME);
        }

        if ($constant === 'class') {
            return $class;
        }

        $classConstNode = $this->parsedNodesByType->findClassConstant($class, $constant);

        if ($classConstNode === null) {
            // fallback to the name
            return $class . '::' . $constant;
        }

        return $this->constExprEvaluator->evaluateDirectly($classConstNode->consts[0]->value);
    }

    /**
     * @return mixed[]
     */
    private function extractConstantArrayTypeValue(ConstantArrayType $constantArrayType): array
    {
        $keys = [];
        foreach ($constantArrayType->getKeyTypes() as $i => $keyType) {
            /** @var ConstantScalarType $keyType */
            $keys[$i] = $keyType->getValue();
        }

        $values = [];
        foreach ($constantArrayType->getValueTypes() as $i => $valueType) {
            if ($valueType instanceof ConstantArrayType) {
                $value = $this->extractConstantArrayTypeValue($valueType);
            } else {
                /** @var ConstantScalarType $valueType */
                $value = $valueType->getValue();
            }
            $values[$keys[$i]] = $value;
        }

        return $values;
    }
}
