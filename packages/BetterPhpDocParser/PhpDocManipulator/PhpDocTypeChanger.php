<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocManipulator;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Rector\BetterPhpDocParser\Guard\NewPhpDocFromPHPStanTypeGuard;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareIntersectionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareCallableTypeNode;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;
final class PhpDocTypeChanger
{
    /**
     * @var array<class-string<Node>>
     */
    public const ALLOWED_TYPES = [GenericTypeNode::class, SpacingAwareArrayTypeNode::class, SpacingAwareCallableTypeNode::class, ArrayShapeNode::class];
    /**
     * @var string[]
     */
    private const ALLOWED_IDENTIFIER_TYPENODE_TYPES = ['class-string'];
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory
     */
    private $paramPhpDocNodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Comment\CommentsMerger
     */
    private $commentsMerger;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Guard\NewPhpDocFromPHPStanTypeGuard
     */
    private $newPhpDocFromPHPStanTypeGuard;
    public function __construct(StaticTypeMapper $staticTypeMapper, TypeComparator $typeComparator, ParamPhpDocNodeFactory $paramPhpDocNodeFactory, NodeNameResolver $nodeNameResolver, CommentsMerger $commentsMerger, PhpDocInfoFactory $phpDocInfoFactory, NewPhpDocFromPHPStanTypeGuard $newPhpDocFromPHPStanTypeGuard)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
        $this->paramPhpDocNodeFactory = $paramPhpDocNodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->commentsMerger = $commentsMerger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->newPhpDocFromPHPStanTypeGuard = $newPhpDocFromPHPStanTypeGuard;
    }
    public function changeVarType(PhpDocInfo $phpDocInfo, Type $newType) : void
    {
        // better skip, could crash hard
        if ($phpDocInfo->hasInvalidTag('@var')) {
            return;
        }
        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->typeComparator->areTypesEqual($phpDocInfo->getVarType(), $newType)) {
            return;
        }
        // prevent existing type override by mixed
        if (!$phpDocInfo->getVarType() instanceof MixedType && $newType instanceof ConstantArrayType && $newType->getItemType() instanceof NeverType) {
            return;
        }
        if (!$this->newPhpDocFromPHPStanTypeGuard->isLegal($newType)) {
            return;
        }
        // override existing type
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, TypeKind::PROPERTY);
        $currentVarTagValueNode = $phpDocInfo->getVarTagValueNode();
        if ($currentVarTagValueNode !== null) {
            // only change type
            $currentVarTagValueNode->type = $newPHPStanPhpDocType;
            $phpDocInfo->markAsChanged();
        } else {
            // add completely new one
            $varTagValueNode = new VarTagValueNode($newPHPStanPhpDocType, '', '');
            $phpDocInfo->addTagValueNode($varTagValueNode);
        }
    }
    public function changeReturnType(PhpDocInfo $phpDocInfo, Type $newType) : bool
    {
        // better not touch this, can crash
        if ($phpDocInfo->hasInvalidTag('@return')) {
            return \false;
        }
        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->typeComparator->areTypesEqual($phpDocInfo->getReturnType(), $newType)) {
            return \false;
        }
        if (!$this->newPhpDocFromPHPStanTypeGuard->isLegal($newType)) {
            return \false;
        }
        // override existing type
        $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, TypeKind::RETURN);
        $currentReturnTagValueNode = $phpDocInfo->getReturnTagValue();
        if ($currentReturnTagValueNode !== null) {
            // only change type
            $currentReturnTagValueNode->type = $newPHPStanPhpDocType;
            $phpDocInfo->markAsChanged();
        } else {
            // add completely new one
            $returnTagValueNode = new ReturnTagValueNode($newPHPStanPhpDocType, '');
            $phpDocInfo->addTagValueNode($returnTagValueNode);
        }
        return \true;
    }
    public function changeParamType(PhpDocInfo $phpDocInfo, Type $newType, Param $param, string $paramName) : void
    {
        // better skip, could crash hard
        if ($phpDocInfo->hasInvalidTag('@param')) {
            return;
        }
        if (!$this->newPhpDocFromPHPStanTypeGuard->isLegal($newType)) {
            return;
        }
        $phpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, TypeKind::PARAM);
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
        // override existing type
        if ($paramTagValueNode !== null) {
            // already set
            $currentType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($paramTagValueNode->type, $param);
            // avoid overriding better type
            if ($this->typeComparator->isSubtype($currentType, $newType)) {
                return;
            }
            if ($this->typeComparator->areTypesEqual($currentType, $newType)) {
                return;
            }
            $paramTagValueNode->type = $phpDocType;
            $phpDocInfo->markAsChanged();
        } else {
            $paramTagValueNode = $this->paramPhpDocNodeFactory->create($phpDocType, $param);
            $phpDocInfo->addTagValueNode($paramTagValueNode);
        }
    }
    public function isAllowed(TypeNode $typeNode) : bool
    {
        if ($typeNode instanceof BracketsAwareUnionTypeNode || $typeNode instanceof BracketsAwareIntersectionTypeNode) {
            foreach ($typeNode->types as $type) {
                if ($this->isAllowed($type)) {
                    return \true;
                }
            }
        }
        if (\in_array(\get_class($typeNode), self::ALLOWED_TYPES, \true)) {
            return \true;
        }
        if (!$typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        return \in_array((string) $typeNode, self::ALLOWED_IDENTIFIER_TYPENODE_TYPES, \true);
    }
    public function copyPropertyDocToParam(Property $property, Param $param) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($property);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $varTag = $phpDocInfo->getVarTagValueNode();
        if (!$varTag instanceof VarTagValueNode) {
            $this->processKeepComments($property, $param);
            return;
        }
        if ($varTag->description !== '') {
            return;
        }
        $parentNode = $param->getAttribute(AttributeKey::PARENT_NODE);
        $paramVarName = $this->nodeNameResolver->getName($param->var);
        if (!$parentNode instanceof ClassMethod) {
            return;
        }
        if (!$this->isAllowed($varTag->type)) {
            return;
        }
        if (!\is_string($paramVarName)) {
            return;
        }
        $phpDocInfo->removeByType(VarTagValueNode::class);
        $param->setAttribute(AttributeKey::PHP_DOC_INFO, $phpDocInfo);
        $phpDocInfo = $parentNode->getAttribute(AttributeKey::PHP_DOC_INFO);
        $paramType = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType($varTag, $property);
        $this->changeParamType($phpDocInfo, $paramType, $param, $paramVarName);
        $this->processKeepComments($property, $param);
    }
    /**
     * @api doctrine
     */
    public function changeVarTypeNode(PhpDocInfo $phpDocInfo, TypeNode $typeNode) : void
    {
        // add completely new one
        $varTagValueNode = new VarTagValueNode($typeNode, '', '');
        $phpDocInfo->addTagValueNode($varTagValueNode);
    }
    private function processKeepComments(Property $property, Param $param) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($param);
        $varTag = $phpDocInfo->getVarTagValueNode();
        $toBeRemoved = !$varTag instanceof VarTagValueNode;
        $this->commentsMerger->keepComments($param, [$property]);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($param);
        $varTag = $phpDocInfo->getVarTagValueNode();
        if (!$toBeRemoved) {
            return;
        }
        if (!$varTag instanceof VarTagValueNode) {
            return;
        }
        if ($varTag->description !== '') {
            return;
        }
        $phpDocInfo->removeByType(VarTagValueNode::class);
    }
}
