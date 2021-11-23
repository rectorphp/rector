<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix20211123\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements TypeMapperInterface<IntersectionType>
 */
final class IntersectionTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    /**
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @required
     */
    public function autowireIntersectionTypeMapper(\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\IntersectionType::class;
    }
    /**
     * @param \PHPStan\Type\Type $type
     * @param \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind
     */
    public function mapToPHPStanPhpDocTypeNode($type, $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        $intersectionTypesNodes = [];
        foreach ($type->getTypes() as $intersectionedType) {
            $intersectionTypesNodes[] = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($intersectionedType, $typeKind);
        }
        $intersectionTypesNodes = \array_unique($intersectionTypesNodes);
        if (\count($intersectionTypesNodes) === 1) {
            return $intersectionTypesNodes[0];
        }
        return new \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode($intersectionTypesNodes);
    }
    /**
     * @param \PHPStan\Type\Type $type
     * @param \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind
     */
    public function mapToPhpParserNode($type, $typeKind) : ?\PhpParser\Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::INTERSECTION_TYPES)) {
            return null;
        }
        $intersectionedTypeNodes = [];
        foreach ($type->getTypes() as $intersectionedType) {
            $resolvedType = $this->phpStanStaticTypeMapper->mapToPhpParserNode($intersectionedType, $typeKind);
            if (!$resolvedType instanceof \PhpParser\Node\Name) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $intersectionedTypeNodes[] = $resolvedType;
        }
        return new \PhpParser\Node\IntersectionType($intersectionedTypeNodes);
    }
}
