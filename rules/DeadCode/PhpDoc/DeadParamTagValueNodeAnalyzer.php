<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer;
use Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\TypeDeclaration\NodeAnalyzer\ParamAnalyzer;
final class DeadParamTagValueNodeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer
     */
    private $genericTypeNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer
     */
    private $mixedArrayTypeNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    public function __construct(NodeNameResolver $nodeNameResolver, TypeComparator $typeComparator, GenericTypeNodeAnalyzer $genericTypeNodeAnalyzer, MixedArrayTypeNodeAnalyzer $mixedArrayTypeNodeAnalyzer, ParamAnalyzer $paramAnalyzer, PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeComparator = $typeComparator;
        $this->genericTypeNodeAnalyzer = $genericTypeNodeAnalyzer;
        $this->mixedArrayTypeNodeAnalyzer = $mixedArrayTypeNodeAnalyzer;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }
    public function isDead(ParamTagValueNode $paramTagValueNode, FunctionLike $functionLike) : bool
    {
        $param = $this->paramAnalyzer->getParamByName($paramTagValueNode->parameterName, $functionLike);
        if (!$param instanceof Param) {
            return \false;
        }
        if ($param->type === null) {
            return \false;
        }
        if ($param->type instanceof Name && $this->nodeNameResolver->isName($param->type, 'object')) {
            return $paramTagValueNode->type instanceof IdentifierTypeNode && (string) $paramTagValueNode->type === 'object';
        }
        if (!$this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($param->type, $paramTagValueNode->type, $functionLike)) {
            return \false;
        }
        if ($this->phpDocTypeChanger->isAllowed($paramTagValueNode->type)) {
            return \false;
        }
        if (!$paramTagValueNode->type instanceof BracketsAwareUnionTypeNode) {
            return $this->isEmptyDescription($paramTagValueNode, $param->type);
        }
        if ($this->mixedArrayTypeNodeAnalyzer->hasMixedArrayType($paramTagValueNode->type)) {
            return \false;
        }
        if (!$this->genericTypeNodeAnalyzer->hasGenericType($paramTagValueNode->type)) {
            return $this->isEmptyDescription($paramTagValueNode, $param->type);
        }
        return \false;
    }
    private function isEmptyDescription(ParamTagValueNode $paramTagValueNode, Node $node) : bool
    {
        if ($paramTagValueNode->description !== '') {
            return \false;
        }
        $parentNode = $paramTagValueNode->getAttribute(PhpDocAttributeKey::PARENT);
        if (!$parentNode instanceof PhpDocTagNode) {
            return \true;
        }
        $parentNode = $parentNode->getAttribute(PhpDocAttributeKey::PARENT);
        if (!$parentNode instanceof PhpDocNode) {
            return \true;
        }
        $children = $parentNode->children;
        foreach ($children as $key => $child) {
            if ($child instanceof PhpDocTagNode && $node instanceof FullyQualified) {
                return $this->isUnionIdentifier($child);
            }
            if (!$this->isTextNextline($key, $child)) {
                return \false;
            }
        }
        return \true;
    }
    private function isTextNextline(int $key, PhpDocChildNode $phpDocChildNode) : bool
    {
        if ($key < 1) {
            return \true;
        }
        if (!$phpDocChildNode instanceof PhpDocTextNode) {
            return \true;
        }
        return (string) $phpDocChildNode === '';
    }
    private function isUnionIdentifier(PhpDocTagNode $phpDocTagNode) : bool
    {
        if (!$phpDocTagNode->value instanceof ParamTagValueNode) {
            return \true;
        }
        if (!$phpDocTagNode->value->type instanceof BracketsAwareUnionTypeNode) {
            return \true;
        }
        $types = $phpDocTagNode->value->type->types;
        foreach ($types as $type) {
            if ($type instanceof IdentifierTypeNode) {
                return \false;
            }
        }
        return \true;
    }
}
