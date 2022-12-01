<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix202212\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements TypeMapperInterface<ClosureType>
 */
final class ClosureTypeMapper implements TypeMapperInterface
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return ClosureType::class;
    }
    /**
     * @param ClosureType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        $identifierTypeNode = new IdentifierTypeNode($type->getClassName());
        $returnDocTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($type->getReturnType(), $typeKind);
        $parameterDocTypeNodes = [];
        foreach ($type->getParameters() as $parameterReflection) {
            $parameterDocTypeNodes[] = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($parameterReflection->getType(), $typeKind);
        }
        return new SpacingAwareCallableTypeNode($identifierTypeNode, $parameterDocTypeNodes, $returnDocTypeNode);
    }
    /**
     * @param TypeKind::* $typeKind
     * @param ClosureType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if ($typeKind === TypeKind::PROPERTY) {
            return null;
        }
        return new FullyQualified('Closure');
    }
    /**
     * @required
     */
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
}
