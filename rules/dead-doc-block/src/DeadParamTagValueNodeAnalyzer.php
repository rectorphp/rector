<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class DeadParamTagValueNodeAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var TypeComparator
     */
    private $typeComparator;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        StaticTypeMapper $staticTypeMapper,
        TypeComparator $typeComparator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
    }

    public function isDead(ParamTagValueNode $paramTagValueNode, ClassMethod $classMethod): bool
    {
        $param = $this->matchParamByName($paramTagValueNode->parameterName, $classMethod);
        if ($param === null) {
            return false;
        }

        if ($param->type === null) {
            return false;
        }

        $phpParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $docParamType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $paramTagValueNode->type,
            $classMethod
        );

        $areTypesEqual = $this->typeComparator->areTypesEqual($docParamType, $phpParamType);
        if (! $areTypesEqual) {
            return false;
        }

        return $paramTagValueNode->description === '';
    }

    private function matchParamByName(string $desiredParamName, ClassMethod $classMethod): ?Param
    {
        foreach ((array) $classMethod->params as $param) {
            $paramName = $this->nodeNameResolver->getName($param);
            if ('$' . $paramName !== $desiredParamName) {
                continue;
            }

            return $param;
        }

        return null;
    }
}
