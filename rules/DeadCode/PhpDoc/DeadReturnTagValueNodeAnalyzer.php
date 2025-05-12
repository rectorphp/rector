<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\DeadCode\PhpDoc\Guard\StandaloneTypeRemovalGuard;
use Rector\DeadCode\PhpDoc\Guard\TemplateTypeRemovalGuard;
use Rector\DeadCode\TypeNodeAnalyzer\GenericTypeNodeAnalyzer;
use Rector\DeadCode\TypeNodeAnalyzer\MixedArrayTypeNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class DeadReturnTagValueNodeAnalyzer
{
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    /**
     * @readonly
     */
    private GenericTypeNodeAnalyzer $genericTypeNodeAnalyzer;
    /**
     * @readonly
     */
    private MixedArrayTypeNodeAnalyzer $mixedArrayTypeNodeAnalyzer;
    /**
     * @readonly
     */
    private StandaloneTypeRemovalGuard $standaloneTypeRemovalGuard;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private TemplateTypeRemovalGuard $templateTypeRemovalGuard;
    public function __construct(TypeComparator $typeComparator, GenericTypeNodeAnalyzer $genericTypeNodeAnalyzer, MixedArrayTypeNodeAnalyzer $mixedArrayTypeNodeAnalyzer, StandaloneTypeRemovalGuard $standaloneTypeRemovalGuard, PhpDocTypeChanger $phpDocTypeChanger, StaticTypeMapper $staticTypeMapper, TemplateTypeRemovalGuard $templateTypeRemovalGuard)
    {
        $this->typeComparator = $typeComparator;
        $this->genericTypeNodeAnalyzer = $genericTypeNodeAnalyzer;
        $this->mixedArrayTypeNodeAnalyzer = $mixedArrayTypeNodeAnalyzer;
        $this->standaloneTypeRemovalGuard = $standaloneTypeRemovalGuard;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->templateTypeRemovalGuard = $templateTypeRemovalGuard;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function isDead(ReturnTagValueNode $returnTagValueNode, $functionLike) : bool
    {
        $returnType = $functionLike->getReturnType();
        if ($returnType === null) {
            return \false;
        }
        if ($returnTagValueNode->description !== '') {
            return \false;
        }
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($returnTagValueNode->type, $functionLike);
        if (!$this->templateTypeRemovalGuard->isLegal($docType)) {
            return \false;
        }
        $scope = $functionLike->getAttribute(AttributeKey::SCOPE);
        if ($scope instanceof Scope && $scope->isInTrait() && $returnTagValueNode->type instanceof ThisTypeNode) {
            return \false;
        }
        if (!$this->typeComparator->arePhpParserAndPhpStanPhpDocTypesEqual($returnType, $returnTagValueNode->type, $functionLike)) {
            return $this->isDeadNotEqual($returnTagValueNode, $returnType, $functionLike);
        }
        if ($this->phpDocTypeChanger->isAllowed($returnTagValueNode->type)) {
            return \false;
        }
        if (!$returnTagValueNode->type instanceof BracketsAwareUnionTypeNode) {
            return $this->standaloneTypeRemovalGuard->isLegal($returnTagValueNode->type, $returnType);
        }
        if ($this->genericTypeNodeAnalyzer->hasGenericType($returnTagValueNode->type)) {
            return \false;
        }
        if ($this->mixedArrayTypeNodeAnalyzer->hasMixedArrayType($returnTagValueNode->type)) {
            return \false;
        }
        return !$this->hasTrueFalsePseudoType($returnTagValueNode->type);
    }
    private function isVoidReturnType(Node $node) : bool
    {
        return $node instanceof Identifier && $node->toString() === 'void';
    }
    private function isNeverReturnType(Node $node) : bool
    {
        return $node instanceof Identifier && $node->toString() === 'never';
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function isDeadNotEqual(ReturnTagValueNode $returnTagValueNode, Node $node, $functionLike) : bool
    {
        if ($returnTagValueNode->type instanceof IdentifierTypeNode && (string) $returnTagValueNode->type === 'void') {
            return \true;
        }
        if (!$this->hasUsefulPhpdocType($returnTagValueNode, $node)) {
            return \true;
        }
        $nodeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node);
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($returnTagValueNode->type, $functionLike);
        return $docType instanceof UnionType && $this->typeComparator->areTypesEqual(TypeCombinator::removeNull($docType), $nodeType);
    }
    private function hasTrueFalsePseudoType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode) : bool
    {
        $unionTypes = $bracketsAwareUnionTypeNode->types;
        foreach ($unionTypes as $unionType) {
            if (!$unionType instanceof IdentifierTypeNode) {
                continue;
            }
            $name = \strtolower((string) $unionType);
            if (\in_array($name, ['true', 'false'], \true)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * exact different between @return and node return type
     * @param mixed $returnType
     */
    private function hasUsefulPhpdocType(ReturnTagValueNode $returnTagValueNode, $returnType) : bool
    {
        if ($returnTagValueNode->type instanceof IdentifierTypeNode && $returnTagValueNode->type->name === 'mixed') {
            return \false;
        }
        if (!$this->isVoidReturnType($returnType)) {
            return !$this->isNeverReturnType($returnType);
        }
        if (!$returnTagValueNode->type instanceof IdentifierTypeNode || (string) $returnTagValueNode->type !== 'never') {
            return \false;
        }
        return !$this->isNeverReturnType($returnType);
    }
}
