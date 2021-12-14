<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\NodeTypeAnalyzer;

use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\TypeAnalyzer\UnionTypeAnalyzer;

final class PropertyTypeDecorator
{
    public function __construct(
        private readonly UnionTypeAnalyzer $unionTypeAnalyzer,
        private readonly PhpDocTypeChanger $phpDocTypeChanger,
        private readonly PhpVersionProvider $phpVersionProvider,
        private readonly NodeFactory $nodeFactory,
    ) {
    }

    public function decoratePropertyUnionType(
        UnionType $unionType,
        Name|ComplexType $typeNode,
        Property $property,
        PhpDocInfo $phpDocInfo
    ): void {
        if ($this->unionTypeAnalyzer->isNullable($unionType)) {
            $property->type = $typeNode;

            $propertyProperty = $property->props[0];

            // add null default
            if ($propertyProperty->default === null) {
                $propertyProperty->default = $this->nodeFactory->createNull();
            }

            return;
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
            $property->type = $typeNode;
            return;
        }

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $unionType);
    }
}
