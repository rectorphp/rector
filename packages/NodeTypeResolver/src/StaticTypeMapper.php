<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareUnionTypeNode;
use Rector\Exception\NotImplementedException;
use Rector\Exception\ShouldNotHappenException;
use Rector\Php\PhpVersionProvider;

/**
 * Inspired by @see StaticTypeToStringResolver
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

    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
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
}
