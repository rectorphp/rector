<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\Type;
use Rector\Enum\ObjectReference;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * PHPStan resolves "parent" to this type when the parent class is unknown at analysis time,
 * typically inside a trait, where "parent" is only bound once the trait is used by a class.
 * PHP accepts "parent" as a native type in traits, so it is kept as-is.
 *
 * @implements TypeMapperInterface<NonexistentParentClassType>
 * @see \Rector\Tests\PHPStanStaticTypeMapper\TypeMapper\NonexistentParentClassTypeMapperTest
 */
final class NonexistentParentClassTypeMapper implements TypeMapperInterface
{
    /**
     * @return array<class-string<Type>>
     */
    public function getNodeClasses(): array
    {
        return [NonexistentParentClassType::class];
    }
    /**
     * @param NonexistentParentClassType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param NonexistentParentClassType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): Name
    {
        return new Name(ObjectReference::PARENT);
    }
}
