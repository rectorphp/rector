<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIntersectionTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;

final class IntersectionTypeMapper implements TypeMapperInterface
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    /**
     * @required
     */
    public function autowireIntersectionTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }

    public function getNodeClass(): string
    {
        return IntersectionType::class;
    }

    /**
     * @param IntersectionType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        $intersectionTypesNodes = [];

        foreach ($type->getTypes() as $intersectionedType) {
            $intersectionTypesNodes[] = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($intersectionedType);
        }

        $intersectionTypesNodes = array_unique($intersectionTypesNodes);

        return new AttributeAwareIntersectionTypeNode($intersectionTypesNodes);
    }

    /**
     * @param IntersectionType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        // intersection types in PHP are not yet supported
        return null;
    }

    /**
     * @param IntersectionType $type
     */
    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        $stringTypes = [];

        foreach ($type->getTypes() as $unionedType) {
            $stringTypes[] = $this->phpStanStaticTypeMapper->mapToDocString($unionedType);
        }

        // remove empty values, e.g. void/iterable
        $stringTypes = array_unique($stringTypes);
        $stringTypes = array_filter($stringTypes);

        return implode('&', $stringTypes);
    }
}
