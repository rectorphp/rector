<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class NeverTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return NeverType::class;
    }

    /**
     * @param NeverType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return new IdentifierTypeNode('mixed');
    }

    /**
     * @param NeverType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        return null;
    }

    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return 'mixed';
    }
}
