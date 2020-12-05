<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\PHPStan\TypeComparator;

final class DeadParamTagValueNodeAnalyzer
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

    public function isDead(ParamTagValueNode $paramTagValueNode, ClassMethod $classMethod): bool
    {
        $param = $this->matchParamByName($paramTagValueNode->parameterName, $classMethod);
        if ($param === null) {
            return false;
        }

        if ($param->type === null) {
            return false;
        }

        if (! $this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual(
            $param->type,
            $paramTagValueNode->type,
            $classMethod
        )) {
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
