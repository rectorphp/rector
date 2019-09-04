<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareUnionTypeNode;
use Rector\Exception\NotImplementedException;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php\PhpVersionProvider;
use Rector\PhpParser\Node\Manipulator\ConstFetchManipulator;

/**
 * Maps PhpParser <=> PHPStan <=> PHPStan doc <=> string type nodes to between all possible formats
 */
final class StaticTypeMapper
{
    /**
     * @var string
     */
    private const PHP_VERSION_SCALAR_TYPES = '7.0';

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    /**
     * @var ConstFetchManipulator
     */
    private $constFetchManipulator;

    public function __construct(
        PhpVersionProvider $phpVersionProvider,
        ConstFetchManipulator $constFetchManipulator
    ) {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->constFetchManipulator = $constFetchManipulator;
    }

    /**
     * @return string[]
     */
    public function mapPHPStanTypeToStrings(Type $currentPHPStanType): array
    {
        if ($currentPHPStanType instanceof ObjectType) {
            return [$currentPHPStanType->getClassName()];
        }

        if ($currentPHPStanType instanceof IntegerType) {
            return ['int'];
        }

        if ($currentPHPStanType instanceof ObjectWithoutClassType) {
            return ['object'];
        }

        if ($currentPHPStanType instanceof ClosureType) {
            return ['callable'];
        }

        if ($currentPHPStanType instanceof CallableType) {
            return ['callable'];
        }

        if ($currentPHPStanType instanceof FloatType) {
            return ['float'];
        }

        if ($currentPHPStanType instanceof BooleanType) {
            return ['bool'];
        }

        if ($currentPHPStanType instanceof StringType) {
            return ['string'];
        }

        if ($currentPHPStanType instanceof NullType) {
            return ['null'];
        }

        if ($currentPHPStanType instanceof MixedType) {
            return ['mixed'];
        }

        if ($currentPHPStanType instanceof ConstantArrayType) {
            return $this->resolveConstantArrayType($currentPHPStanType);
        }

        if ($currentPHPStanType instanceof ArrayType) {
            $types = $this->mapPHPStanTypeToStrings($currentPHPStanType->getItemType());

            if ($types === []) {
                return ['array'];
            }

            foreach ($types as $key => $type) {
                $types[$key] = $type . '[]';
            }

            return array_unique($types);
        }

        if ($currentPHPStanType instanceof UnionType) {
            $types = [];
            foreach ($currentPHPStanType->getTypes() as $singleStaticType) {
                $currentIterationTypes = $this->mapPHPStanTypeToStrings($singleStaticType);
                $types = array_merge($types, $currentIterationTypes);
            }

            return $types;
        }

        if ($currentPHPStanType instanceof IntersectionType) {
            $types = [];
            foreach ($currentPHPStanType->getTypes() as $singleStaticType) {
                $currentIterationTypes = $this->mapPHPStanTypeToStrings($singleStaticType);
                $types = array_merge($types, $currentIterationTypes);
            }

            return $this->removeGenericArrayTypeIfThereIsSpecificArrayType($types);
        }

        if ($currentPHPStanType instanceof NeverType) {
            return [];
        }

        if ($currentPHPStanType instanceof ThisType) {
            // @todo what is desired return value?
            return [$currentPHPStanType->getClassName()];
        }

        throw new NotImplementedException();
    }

    public function mapPHPStanTypeToPHPStanPhpDocTypeNode(Type $currentPHPStanType): ?TypeNode
    {
        if ($currentPHPStanType instanceof UnionType) {
            $unionTypesNodes = [];
            foreach ($currentPHPStanType->getTypes() as $unionedType) {
                $unionTypesNodes[] = $this->mapPHPStanTypeToPHPStanPhpDocTypeNode($unionedType);
            }

            return new AttributeAwareUnionTypeNode($unionTypesNodes);
        }

        if ($currentPHPStanType instanceof ArrayType) {
            $itemTypeNode = $this->mapPHPStanTypeToPHPStanPhpDocTypeNode($currentPHPStanType->getItemType());
            if ($itemTypeNode === null) {
                throw new ShouldNotHappenException();
            }

            return new ArrayTypeNode($itemTypeNode);
        }

        if ($currentPHPStanType instanceof IntegerType) {
            return new IdentifierTypeNode('int');
        }

        if ($currentPHPStanType instanceof StringType) {
            return new IdentifierTypeNode('string');
        }

        if ($currentPHPStanType instanceof FloatType) {
            return new IdentifierTypeNode('float');
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($currentPHPStanType));
    }

    /**
     * @return Identifier|Name|NullableType|null
     */
    public function mapPHPStanTypeToPhpParserNode(Type $currentPHPStanType): ?Node
    {
        if ($currentPHPStanType instanceof IntegerType) {
            if ($this->phpVersionProvider->isAtLeast(self::PHP_VERSION_SCALAR_TYPES)) {
                return new Identifier('int');
            }

            return null;
        }

        if ($currentPHPStanType instanceof StringType) {
            if ($this->phpVersionProvider->isAtLeast(self::PHP_VERSION_SCALAR_TYPES)) {
                return new Identifier('string');
            }

            return null;
        }

        if ($currentPHPStanType instanceof BooleanType) {
            if ($this->phpVersionProvider->isAtLeast(self::PHP_VERSION_SCALAR_TYPES)) {
                return new Identifier('bool');
            }

            return null;
        }

        if ($currentPHPStanType instanceof FloatType) {
            if ($this->phpVersionProvider->isAtLeast(self::PHP_VERSION_SCALAR_TYPES)) {
                return new Identifier('float');
            }

            return null;
        }

        if ($currentPHPStanType instanceof ArrayType) {
            return new Identifier('array');
        }

        if ($currentPHPStanType instanceof ObjectType) {
            return new FullyQualified($currentPHPStanType->getClassName());
        }

        if ($currentPHPStanType instanceof UnionType) {
            return null;
        }

        if ($currentPHPStanType instanceof MixedType) {
            return null;
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($currentPHPStanType));
    }

    public function mapPhpParserNodeToString(Expr $expr): string
    {
        if ($expr instanceof LNumber) {
            return 'int';
        }

        if ($expr instanceof Array_) {
            return 'mixed[]';
        }

        if ($expr instanceof DNumber) {
            return 'float';
        }

        /** @var Scope $scope */
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        $exprStaticType = $scope->getType($expr);

        if ($exprStaticType instanceof IntegerType) {
            return 'int';
        }

        if ($exprStaticType instanceof StringType) {
            return 'string';
        }

        if ($this->constFetchManipulator->isBool($expr)) {
            return 'bool';
        }

        return '';
    }

    /**
     * @return string[]
     */
    private function resolveConstantArrayType(ConstantArrayType $constantArrayType): array
    {
        $arrayTypes = [];

        foreach ($constantArrayType->getValueTypes() as $valueType) {
            $arrayTypes = array_merge($arrayTypes, $this->mapPHPStanTypeToStrings($valueType));
        }

        $arrayTypes = array_unique($arrayTypes);

        return array_map(function (string $arrayType): string {
            return $arrayType . '[]';
        }, $arrayTypes);
    }

    /**
     * Removes "array" if there is "SomeType[]" already
     *
     * @param string[] $types
     * @return string[]
     */
    private function removeGenericArrayTypeIfThereIsSpecificArrayType(array $types): array
    {
        $hasSpecificArrayType = false;
        foreach ($types as $key => $type) {
            if (Strings::endsWith($type, '[]')) {
                $hasSpecificArrayType = true;
                break;
            }
        }

        if ($hasSpecificArrayType === false) {
            return $types;
        }

        foreach ($types as $key => $type) {
            if ($type === 'array') {
                unset($types[$key]);
            }
        }

        return $types;
    }
}
