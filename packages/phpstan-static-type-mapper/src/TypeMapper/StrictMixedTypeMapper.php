<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\StrictMixedType;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class StrictMixedTypeMapper implements TypeMapperInterface
{
    /**
     * @var string
     */
    private const MIXED = 'mixed';

    public function getNodeClass(): string
    {
        return StrictMixedType::class;
    }

    /**
     * @param StrictMixedType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return new AttributeAwareIdentifierTypeNode(self::MIXED);
    }

    /**
     * @param StrictMixedType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        return new Name(self::MIXED);
    }

    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return self::MIXED;
    }
}
