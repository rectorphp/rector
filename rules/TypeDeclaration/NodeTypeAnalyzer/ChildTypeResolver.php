<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;

final class ChildTypeResolver
{
    public function __construct(
        private StaticTypeMapper $staticTypeMapper
    ) {
    }

    /**
     * @return Name|NullableType|UnionType|null
     */
    public function resolveChildTypeNode(Type $type): ?Node
    {
        if ($type instanceof MixedType) {
            return null;
        }

        if ($type instanceof SelfObjectType || $type instanceof StaticType) {
            $type = new ObjectType($type->getClassName());
        }

        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type);
    }
}
