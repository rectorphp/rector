<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PhpDocParser\PhpDocInfoAnalyzer;
use Rector\TypeDeclaration\Guard\PhpDocNestedAnnotationGuard;
use Rector\TypeDeclaration\Helper\PhpDocNullableTypeHelper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\VarAnnotationIncorrectNullableRectorTest
 */
final class VarAnnotationIncorrectNullableRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
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
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\PhpDocInfoAnalyzer
     */
    private $phpDocInfoAnalyzer;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, PhpDocNullableTypeHelper $phpDocNullableTypeHelper, PhpDocNestedAnnotationGuard $phpDocNestedAnnotationGuard, PhpVersionProvider $phpVersionProvider, PhpDocInfoAnalyzer $phpDocInfoAnalyzer)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocNullableTypeHelper = $phpDocNullableTypeHelper;
        $this->phpDocNestedAnnotationGuard = $phpDocNestedAnnotationGuard;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->phpDocInfoAnalyzer = $phpDocInfoAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add or remove null type from @var phpdoc typehint based on php property type declaration', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var DateTime[]
     */
    private ?array $dateTimes;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var DateTime[]|null
     */
    private ?array $dateTimes;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (\count($node->props) !== 1) {
            return null;
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }
        if (!$this->phpDocNestedAnnotationGuard->isPhpDocCommentCorrectlyParsed($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (!$this->phpDocInfoAnalyzer->isVarDocAlreadySet($phpDocInfo)) {
            return null;
        }
        if ($node->type === null) {
            return null;
        }
        $phpParserType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->type);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        if ($varTagValueNode->type === null) {
            return null;
        }
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $node);
        $updatedPhpDocType = $this->phpDocNullableTypeHelper->resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserType($docType, $phpParserType);
        if (!$updatedPhpDocType instanceof Type) {
            return null;
        }
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $updatedPhpDocType);
        if (!$phpDocInfo->hasChanged()) {
            return null;
        }
        return $node;
    }
}
