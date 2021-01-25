<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\HasOffsetType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class HasOffsetTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return HasOffsetType::class;
    }

    /**
     * @param HasOffsetType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return new ArrayTypeNode(new IdentifierTypeNode('mixed'));
    }

    /**
     * @param HasOffsetType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        throw new ShouldNotHappenException();
    }

    /**
     * @param HasOffsetType $type
     */
    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return 'mixed[]';
    }
}
