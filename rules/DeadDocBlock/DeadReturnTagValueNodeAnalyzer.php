<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock;

use PhpParser\Node\FunctionLike;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareGenericTypeNode;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;

final class DeadReturnTagValueNodeAnalyzer
{
    /**
     * @var TypeComparator
     */
    private $typeComparator;

    public function __construct(TypeComparator $typeComparator)
    {
        $this->typeComparator = $typeComparator;
    }

    public function isDead(ReturnTagValueNode $returnTagValueNode, FunctionLike $functionLike): bool
    {
        $returnType = $functionLike->getReturnType();
        if ($returnType === null) {
            return false;
        }

        if (! $this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($returnType, $returnTagValueNode->type,
            $functionLike
        )) {
            return false;
        }

        if ($returnTagValueNode->type instanceof AttributeAwareGenericTypeNode) {
            return false;
        }

        return $returnTagValueNode->description === '';
    }
}
