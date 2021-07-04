<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\StaticType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;

/**
 * @see \Rector\Tests\NodeTypeResolver\StaticTypeMapper\StaticTypeMapperTest
 *
 * @implements TypeMapperInterface<StaticType>
 */
final class StaticTypeMapper implements TypeMapperInterface
{
    public function __construct(
        private PhpVersionProvider $phpVersionProvider
    ) {
    }

    /**
     * @return class-string<Type>
     */
    public function getNodeClass(): string
    {
        return StaticType::class;
    }

    /**
     * @param StaticType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, TypeKind $typeKind): TypeNode
    {
        return new ThisTypeNode();
    }

    /**
     * @param StaticType $type
     */
    public function mapToPhpParserNode(Type $type, TypeKind $typeKind): ?Node
    {
        if ($type instanceof ThisType) {
            // @todo wait for PHPStan to differentiate between self/static
            if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::STATIC_RETURN_TYPE)) {
                return new Name('static');
            }

            return new Name('self');
        }

        return null;
    }
}
