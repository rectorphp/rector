<?php

declare(strict_types=1);

namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;

final class DeadParamTagValueNodeAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private TypeComparator $typeComparator
    ) {
    }

    public function isDead(ParamTagValueNode $paramTagValueNode, FunctionLike $functionLike): bool
    {
        $param = $this->matchParamByName($paramTagValueNode->parameterName, $functionLike);
        if (! $param instanceof Param) {
            return false;
        }

        if ($param->type === null) {
            return false;
        }

        if ($param->type instanceof Name && $this->nodeNameResolver->isName($param->type, 'object')) {
            return $paramTagValueNode->type instanceof IdentifierTypeNode && (string) $paramTagValueNode->type === 'object';
        }

        if (! $this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual(
            $param->type,
            $paramTagValueNode->type,
            $functionLike
        )) {
            return false;
        }

        if (in_array($paramTagValueNode->type::class, [
            GenericTypeNode::class,
            SpacingAwareCallableTypeNode::class,
        ], true)) {
            return false;
        }

        if (! $paramTagValueNode->type instanceof BracketsAwareUnionTypeNode) {
            return $paramTagValueNode->description === '';
        }

        if (! $this->hasGenericType($paramTagValueNode->type)) {
            return $paramTagValueNode->description === '';
        }

        return false;
    }

    private function hasGenericType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode): bool
    {
        $types = $bracketsAwareUnionTypeNode->types;

        foreach ($types as $type) {
            if ($type instanceof GenericTypeNode) {
                if ($type->type instanceof IdentifierTypeNode && $type->type->name === 'array') {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    private function matchParamByName(string $desiredParamName, FunctionLike $functionLike): ?Param
    {
        foreach ($functionLike->getParams() as $param) {
            $paramName = $this->nodeNameResolver->getName($param);
            if ('$' . $paramName !== $desiredParamName) {
                continue;
            }

            return $param;
        }

        return null;
    }
}
