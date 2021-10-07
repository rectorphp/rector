<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
final class DeadParamTagValueNodeAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeComparator = $typeComparator;
    }
    public function isDead(\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode $paramTagValueNode, \PhpParser\Node\FunctionLike $functionLike) : bool
    {
        $param = $this->matchParamByName($paramTagValueNode->parameterName, $functionLike);
        if (!$param instanceof \PhpParser\Node\Param) {
            return \false;
        }
        if ($param->type === null) {
            return \false;
        }
        if ($param->type instanceof \PhpParser\Node\Name && $this->nodeNameResolver->isName($param->type, 'object')) {
            return $paramTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode && (string) $paramTagValueNode->type === 'object';
        }
        if (!$this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($param->type, $paramTagValueNode->type, $functionLike)) {
            return \false;
        }
        if (\in_array(\get_class($paramTagValueNode->type), [\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode::class, \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode::class], \true)) {
            return \false;
        }
        if (!$paramTagValueNode->type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode) {
            return $this->isEmptyDescription($paramTagValueNode);
        }
        if (!$this->hasGenericType($paramTagValueNode->type)) {
            return $this->isEmptyDescription($paramTagValueNode);
        }
        return \false;
    }
    private function isEmptyDescription(\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode $paramTagValueNode) : bool
    {
        if ($paramTagValueNode->description !== '') {
            return \false;
        }
        $parent = $paramTagValueNode->getAttribute('parent');
        if (!$parent instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode) {
            return \true;
        }
        $parent = $parent->getAttribute('parent');
        if (!$parent instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode) {
            return \true;
        }
        $children = $parent->children;
        if (!isset($children[1])) {
            return \true;
        }
        if (!$children[1] instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode) {
            return \true;
        }
        return (string) $children[1] === '';
    }
    private function hasGenericType(\Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode) : bool
    {
        $types = $bracketsAwareUnionTypeNode->types;
        foreach ($types as $type) {
            if ($type instanceof \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode) {
                if ($type->type instanceof \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode && $type->type->name === 'array') {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
    private function matchParamByName(string $desiredParamName, \PhpParser\Node\FunctionLike $functionLike) : ?\PhpParser\Node\Param
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
