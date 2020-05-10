<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\PHPStan\Type\SelfObjectType;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class SelfObjectTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return SelfObjectType::class;
    }

    /**
     * @param SelfObjectType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return new IdentifierTypeNode('self');
    }

    /**
     * @param SelfObjectType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        return new Name('self');
    }

    /**
     * @param SelfObjectType $type
     */
    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return $type->describe(VerbosityLevel::typeOnly());
    }
}
