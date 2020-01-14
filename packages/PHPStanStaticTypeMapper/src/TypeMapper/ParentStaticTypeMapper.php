<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\PHPStan\Type\ParentStaticType;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class ParentStaticTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return ParentStaticType::class;
    }

    /**
     * @param ParentStaticType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return new IdentifierTypeNode('parent');
    }

    /**
     * @param ParentStaticType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        return new Identifier('parent');
    }
}
