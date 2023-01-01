<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\IterableType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix202301\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements TypeMapperInterface<IterableType>
 */
final class IterableTypeMapper implements TypeMapperInterface
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    /**
     * @required
     */
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return IterableType::class;
    }
    /**
     * @param IterableType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        $itemTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($type->getItemType(), $typeKind);
        if ($itemTypeNode instanceof UnionTypeNode) {
            return $this->convertUnionArrayTypeNodesToArrayTypeOfUnionTypeNodes($itemTypeNode);
        }
        return new SpacingAwareArrayTypeNode($itemTypeNode);
    }
    /**
     * @param IterableType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        return new Name('iterable');
    }
    private function convertUnionArrayTypeNodesToArrayTypeOfUnionTypeNodes(UnionTypeNode $unionTypeNode) : BracketsAwareUnionTypeNode
    {
        $unionedArrayType = [];
        foreach ($unionTypeNode->types as $unionedType) {
            if ($unionedType instanceof UnionTypeNode) {
                foreach ($unionedType->types as $key => $subUnionedType) {
                    $unionedType->types[$key] = new ArrayTypeNode($subUnionedType);
                }
                $unionedArrayType[] = $unionedType;
                continue;
            }
            $unionedArrayType[] = new ArrayTypeNode($unionedType);
        }
        return new BracketsAwareUnionTypeNode($unionedArrayType);
    }
}
