<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;

final class DeadVarTagValueNodeAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var TypeComparator
     */
    private $typeComparator;

    public function __construct(NodeNameResolver $nodeNameResolver, TypeComparator $typeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeComparator = $typeComparator;
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
