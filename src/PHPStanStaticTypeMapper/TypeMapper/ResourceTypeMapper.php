<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ResourceType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<ResourceType>
 */
final class ResourceTypeMapper implements TypeMapperInterface
{
    /**
     * @return array<class-string<Type>>
     */
    public function getNodeClasses(): array
    {
        return [ResourceType::class];
    }
    /**
     * @param ResourceType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param ResourceType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?Node
    {
        return null;
    }
}
