<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\IterableType;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<IterableType>
 */
final class IterableTypeMapper implements TypeMapperInterface
{
    /**
     * @return array<class-string<Type>>
     */
    public function getNodeClasses(): array
    {
        return [IterableType::class];
    }
    /**
     * @param IterableType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param IterableType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): Identifier
    {
        return new Identifier('iterable');
    }
}
