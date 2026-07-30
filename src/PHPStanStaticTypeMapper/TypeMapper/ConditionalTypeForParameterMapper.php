<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ConditionalTypeForParameter;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
/**
 * @implements TypeMapperInterface<ConditionalTypeForParameter>
 */
final class ConditionalTypeForParameterMapper implements TypeMapperInterface
{
    private PHPStanStaticTypeMapper $phpStanStaticTypeMapper;
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    /**
     * @return array<class-string<Type>>
     */
    public function getNodeClasses(): array
    {
        return [ConditionalTypeForParameter::class];
    }
    /**
     * @param ConditionalTypeForParameter $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param ConditionalTypeForParameter $type
     * @param TypeKind::* $typeKind
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?Node
    {
        $type = TypeCombinator::union($type->getIf(), $type->getElse());
        return $this->phpStanStaticTypeMapper->mapToPhpParserNode($type, $typeKind);
    }
}
