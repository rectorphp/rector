<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @implements TypeMapperInterface<CallableType>
 */
final class CallableTypeMapper implements TypeMapperInterface
{
    private PHPStanStaticTypeMapper $phpStanStaticTypeMapper;

    #[Required]
    public function autowireCallableTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }

    /**
     * @return class-string<Type>
     */
    public function getNodeClass(): string
    {
        return CallableType::class;
    }

    /**
     * @param CallableType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, TypeKind $typeKind): TypeNode
    {
        $returnTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($type->getReturnType(), $typeKind);

        return new SpacingAwareCallableTypeNode(new IdentifierTypeNode('callable'), [], $returnTypeNode);
    }

    /**
     * @param CallableType|ClosureType $type
     */
    public function mapToPhpParserNode(Type $type, TypeKind $typeKind): ?Node
    {
        if ($typeKind->equals(TypeKind::PROPERTY())) {
            return null;
        }

        return new Name('callable');
    }
}
