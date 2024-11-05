<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\DeadCode\PhpDoc\Guard\StandaloneTypeRemovalGuard;
use Rector\DeadCode\PhpDoc\Guard\TemplateTypeRemovalGuard;
use Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer;
use Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
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
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\Guard\StandaloneTypeRemovalGuard
     */
    private $standaloneTypeRemovalGuard;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\Guard\TemplateTypeRemovalGuard
     */
    private $templateTypeRemovalGuard;
    public function __construct(NodeNameResolver $nodeNameResolver, TypeComparator $typeComparator, GenericTypeNodeAnalyzer $genericTypeNodeAnalyzer, MixedArrayTypeNodeAnalyzer $mixedArrayTypeNodeAnalyzer, ParamAnalyzer $paramAnalyzer, PhpDocTypeChanger $phpDocTypeChanger, StandaloneTypeRemovalGuard $standaloneTypeRemovalGuard, StaticTypeMapper $staticTypeMapper, TemplateTypeRemovalGuard $templateTypeRemovalGuard)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeComparator = $typeComparator;
        $this->genericTypeNodeAnalyzer = $genericTypeNodeAnalyzer;
        $this->mixedArrayTypeNodeAnalyzer = $mixedArrayTypeNodeAnalyzer;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->standaloneTypeRemovalGuard = $standaloneTypeRemovalGuard;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->templateTypeRemovalGuard = $templateTypeRemovalGuard;
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
        if ($paramTagValueNode->description !== '') {
            return \false;
        }
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($paramTagValueNode->type, $functionLike);
        if (!$this->templateTypeRemovalGuard->isLegal($docType)) {
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
            return $this->standaloneTypeRemovalGuard->isLegal($paramTagValueNode->type, $param->type);
        }
        return $this->isAllowedBracketAwareUnion($paramTagValueNode->type);
    }
    private function isAllowedBracketAwareUnion(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode) : bool
    {
        if ($this->mixedArrayTypeNodeAnalyzer->hasMixedArrayType($bracketsAwareUnionTypeNode)) {
            return \false;
        }
        return !$this->genericTypeNodeAnalyzer->hasGenericType($bracketsAwareUnionTypeNode);
    }
}
