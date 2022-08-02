<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix202208\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements TypeMapperInterface<IntersectionType>
 */
final class IntersectionTypeMapper implements TypeMapperInterface
{
    /**
     * @var string
     */
    private const STRING = 'string';
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
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
        return IntersectionType::class;
    }
    /**
     * @param IntersectionType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, string $typeKind) : TypeNode
    {
        $intersectionTypesNodes = [];
        foreach ($type->getTypes() as $intersectionedType) {
            $intersectionTypesNodes[] = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($intersectionedType, $typeKind);
        }
        $intersectionTypesNodes = \array_unique($intersectionTypesNodes);
        if (\count($intersectionTypesNodes) === 1) {
            return $intersectionTypesNodes[0];
        }
        return new BracketsAwareIntersectionTypeNode($intersectionTypesNodes);
    }
    /**
     * @param IntersectionType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::INTERSECTION_TYPES)) {
            return null;
        }
        $intersectionedTypeNodes = [];
        foreach ($type->getTypes() as $intersectionedType) {
            $resolvedType = $this->phpStanStaticTypeMapper->mapToPhpParserNode($intersectionedType, $typeKind);
            if ($intersectionedType instanceof GenericClassStringType) {
                $resolvedTypeName = self::STRING;
                $resolvedType = new Name(self::STRING);
            } elseif (!$resolvedType instanceof Name) {
                throw new ShouldNotHappenException();
            } else {
                $resolvedTypeName = (string) $resolvedType;
            }
            if (\in_array($resolvedTypeName, [self::STRING, 'object'], \true)) {
                return $resolvedType;
            }
            $intersectionedTypeNodes[] = $resolvedType;
        }
        return new Node\IntersectionType($intersectionedTypeNodes);
    }
}
