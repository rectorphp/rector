<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\Guard\PhpDocNestedAnnotationGuard;
use Rector\TypeDeclaration\Helper\PhpDocNullableTypeHelper;
use Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\ParamAnnotationIncorrectNullableRectorTest
 */
final class ParamAnnotationIncorrectNullableRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\Helper\PhpDocNullableTypeHelper
     */
    private $phpDocNullableTypeHelper;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\Guard\PhpDocNestedAnnotationGuard
     */
    private $phpDocNestedAnnotationGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\PhpDocParser\ParamPhpDocNodeFactory
     */
    private $paramPhpDocNodeFactory;
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(TypeComparator $typeComparator, PhpDocNullableTypeHelper $phpDocNullableTypeHelper, PhpDocNestedAnnotationGuard $phpDocNestedAnnotationGuard, ParamPhpDocNodeFactory $paramPhpDocNodeFactory, PhpVersionProvider $phpVersionProvider)
    {
        $this->typeComparator = $typeComparator;
        $this->phpDocNullableTypeHelper = $phpDocNullableTypeHelper;
        $this->phpDocNestedAnnotationGuard = $phpDocNestedAnnotationGuard;
        $this->paramPhpDocNodeFactory = $paramPhpDocNodeFactory;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add or remove null type from @param phpdoc typehint based on php parameter type declaration', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param \DateTime[] $dateTimes
     */
    public function setDateTimes(?array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param \DateTime[]|null $dateTimes
     */
    public function setDateTimes(?array $dateTimes): self
    {
        $this->dateTimes = $dateTimes;

        return $this;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->getParams() === []) {
            return null;
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }
        if (!$this->phpDocNestedAnnotationGuard->isPhpDocCommentCorrectlyParsed($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        return $this->updateParamTagsIfRequired($phpDocNode, $node, $phpDocInfo);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function matchParamByName(string $desiredParamName, $node) : ?Param
    {
        foreach ($node->getParams() as $param) {
            $paramName = $this->nodeNameResolver->getName($param);
            if ('$' . $paramName !== $desiredParamName) {
                continue;
            }
            return $param;
        }
        return null;
    }
    private function wasUpdateOfParamTypeRequired(PhpDocInfo $phpDocInfo, Type $newType, Param $param, string $paramName) : bool
    {
        // better skip, could crash hard
        if ($phpDocInfo->hasInvalidTag('@param')) {
            return \false;
        }
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType, TypeKind::PARAM);
        $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
        // override existing type
        if ($paramTagValueNode !== null) {
            // already set
            $currentType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($paramTagValueNode->type, $param);
            if ($this->typeComparator->areTypesEqual($currentType, $newType)) {
                return \false;
            }
            $paramTagValueNode->type = $typeNode;
        } else {
            $paramTagValueNode = $this->paramPhpDocNodeFactory->create($typeNode, $param);
            $phpDocInfo->addTagValueNode($paramTagValueNode);
        }
        return \true;
    }
    /**
     * @return ClassMethod|Function_|null
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function updateParamTagsIfRequired(PhpDocNode $phpDocNode, $node, PhpDocInfo $phpDocInfo) : ?Node
    {
        $paramTagValueNodes = $phpDocNode->getParamTagValues();
        $paramTagWasUpdated = \false;
        foreach ($paramTagValueNodes as $paramTagValueNode) {
            if ($paramTagValueNode->type === null) {
                continue;
            }
            $param = $this->matchParamByName($paramTagValueNode->parameterName, $node);
            if (!$param instanceof Param) {
                continue;
            }
            $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($paramTagValueNode->type, $node);
            $updatedPhpDocType = $this->phpDocNullableTypeHelper->resolveUpdatedPhpDocTypeFromPhpDocTypeAndParamNode($docType, $param);
            if (!$updatedPhpDocType instanceof Type) {
                continue;
            }
            if ($this->wasUpdateOfParamTypeRequired($phpDocInfo, $updatedPhpDocType, $param, $paramTagValueNode->parameterName)) {
                $paramTagWasUpdated = \true;
            }
        }
        return $paramTagWasUpdated ? $node : null;
    }
}
