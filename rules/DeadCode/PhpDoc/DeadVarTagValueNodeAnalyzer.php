<?php

declare(strict_types=1);

namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;

final class DeadVarTagValueNodeAnalyzer
{
    public function __construct(
        private TypeComparator $typeComparator
    ) {
    }

    public function isDead(VarTagValueNode $varTagValueNode, Property $property): bool
    {
        if ($property->type === null) {
            return false;
        }

        if (! $this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual(
            $property->type,
            $varTagValueNode->type,
            $property
        )) {
            return false;
        }

        return $varTagValueNode->description === '';
    }
}
