<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PhpDocParser;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
final class TypeExpressionFromVarTagResolver
{
    /**
     * @readonly
     */
    private ScalarStringToTypeMapper $scalarStringToTypeMapper;
    public function __construct(ScalarStringToTypeMapper $scalarStringToTypeMapper)
    {
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
    }
    public function resolveTypeExpressionFromVarTag(TypeNode $typeNode, Variable $variable) : ?Expr
    {
        if ($typeNode instanceof IdentifierTypeNode) {
            $scalarType = $this->scalarStringToTypeMapper->mapScalarStringToType($typeNode->name);
            $scalarTypeFunction = $this->getScalarTypeFunction(\get_class($scalarType));
            if ($scalarTypeFunction !== null) {
                $arg = new Arg($variable);
                return new FuncCall(new Name($scalarTypeFunction), [$arg]);
            }
            if ($scalarType->isNull()->yes()) {
                return new Identical($variable, new ConstFetch(new Name('null')));
            }
            if ($scalarType instanceof ConstantBooleanType) {
                return new Identical($variable, new ConstFetch(new Name($scalarType->getValue() ? 'true' : 'false')));
            }
            if ($scalarType instanceof MixedType && !$scalarType->isExplicitMixed()) {
                return new Instanceof_($variable, new Name($typeNode->name));
            }
        } elseif ($typeNode instanceof NullableTypeNode) {
            $unionExpressions = [];
            $nullableTypeExpression = $this->resolveTypeExpressionFromVarTag($typeNode->type, $variable);
            if (!$nullableTypeExpression instanceof Expr) {
                return null;
            }
            $unionExpressions[] = $nullableTypeExpression;
            $nullExpression = $this->resolveTypeExpressionFromVarTag(new IdentifierTypeNode('null'), $variable);
            \assert($nullExpression instanceof Expr);
            $unionExpressions[] = $nullExpression;
            return $this->generateOrExpression($unionExpressions);
        } elseif ($typeNode instanceof BracketsAwareUnionTypeNode) {
            $unionExpressions = [];
            foreach ($typeNode->types as $typeNode) {
                $unionExpression = $this->resolveTypeExpressionFromVarTag($typeNode, $variable);
                if (!$unionExpression instanceof Expr) {
                    return null;
                }
                $unionExpressions[] = $unionExpression;
            }
            return $this->generateOrExpression($unionExpressions);
        } elseif ($typeNode instanceof BracketsAwareIntersectionTypeNode) {
            $intersectionExpressions = [];
            foreach ($typeNode->types as $typeNode) {
                $intersectionExpression = $this->resolveTypeExpressionFromVarTag($typeNode, $variable);
                if (!$intersectionExpression instanceof Expr) {
                    return null;
                }
                $intersectionExpressions[] = $intersectionExpression;
            }
            return $this->generateAndExpression($intersectionExpressions);
        }
        return null;
    }
    /**
     * @param Expr[] $unionExpressions
     * @return BooleanOr
     */
    private function generateOrExpression(array $unionExpressions)
    {
        $booleanOr = new BooleanOr($unionExpressions[0], $unionExpressions[1]);
        if (\count($unionExpressions) == 2) {
            return $booleanOr;
        }
        \array_splice($unionExpressions, 0, 2, [$booleanOr]);
        return $this->generateOrExpression($unionExpressions);
    }
    /**
     * @param Expr[] $intersectionExpressions
     * @return BooleanAnd
     */
    private function generateAndExpression(array $intersectionExpressions)
    {
        $booleanAnd = new BooleanAnd($intersectionExpressions[0], $intersectionExpressions[1]);
        if (\count($intersectionExpressions) == 2) {
            return $booleanAnd;
        }
        \array_splice($intersectionExpressions, 0, 2, [$booleanAnd]);
        return $this->generateAndExpression($intersectionExpressions);
    }
    /**
     * @param class-string $className
     */
    private function getScalarTypeFunction(string $className) : ?string
    {
        switch ($className) {
            case IntegerType::class:
                return 'is_int';
            case BooleanType::class:
                return 'is_bool';
            case FloatType::class:
                return 'is_float';
            case StringType::class:
                return 'is_string';
            case ArrayType::class:
                return 'is_array';
            case CallableType::class:
                return 'is_callable';
            case ObjectWithoutClassType::class:
                return 'is_object';
            case IterableType::class:
                return 'is_iterable';
            default:
                return null;
        }
    }
}
