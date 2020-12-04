<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class DeadReturnTagValueNodeAnalyzer
{
    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var TypeComparator
     */
    private $typeComparator;

    public function __construct(StaticTypeMapper $staticTypeMapper, TypeComparator $typeComparator)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
    }

    public function isDead(ReturnTagValueNode $returnTagValueNode, ClassMethod $classMethod): bool
    {
        $returnType = $classMethod->getReturnType();
        if ($returnType === null) {
            return false;
        }

        $phpReturnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnType);
        $docReturnType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $returnTagValueNode->type,
            $classMethod
        );

        $areTypesEqual = $this->typeComparator->areTypesEqual($phpReturnType, $docReturnType);
        if (! $areTypesEqual) {
            return false;
        }

        return $returnTagValueNode->description === '';
    }
}
