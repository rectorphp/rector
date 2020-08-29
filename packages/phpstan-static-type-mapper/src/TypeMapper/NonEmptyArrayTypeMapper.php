<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class NonEmptyArrayTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return NonEmptyArrayType::class;
    }

    /**
     * @param NonEmptyArrayType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return new AttributeAwareArrayTypeNode(new AttributeAwareIdentifierTypeNode('mixed'));
    }

    /**
     * @param NonEmptyArrayType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        return new Name('array');
    }

    /**
     * @param NonEmptyArrayType $type
     */
    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return 'mixed[]';
    }
}
