<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Guard\PhpDocNestedAnnotationGuard;
use Rector\TypeDeclaration\Helper\PhpDocNullableTypeHelper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\VarAnnotationIncorrectNullableRector\VarAnnotationIncorrectNullableRectorTest
 */
final class VarAnnotationIncorrectNullableRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\TypeDeclaration\Helper\PhpDocNullableTypeHelper $phpDocNullableTypeHelper, \Rector\TypeDeclaration\Guard\PhpDocNestedAnnotationGuard $phpDocNestedAnnotationGuard, \Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocNullableTypeHelper = $phpDocNullableTypeHelper;
        $this->phpDocNestedAnnotationGuard = $phpDocNestedAnnotationGuard;
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add or remove null type from @var phpdoc typehint based on php property type declaration', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (\count($node->props) !== 1) {
            return null;
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }
        if (!$this->phpDocNestedAnnotationGuard->isPhpDocCommentCorrectlyParsed($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (!$this->isVarDocAlreadySet($phpDocInfo)) {
            return null;
        }
        if ($node->type === null) {
            return null;
        }
        $phpParserType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->type);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            return null;
        }
        if ($varTagValueNode->type === null) {
            return null;
        }
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $node);
        $updatedPhpDocType = $this->phpDocNullableTypeHelper->resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserType($docType, $phpParserType);
        if (!$updatedPhpDocType instanceof \PHPStan\Type\Type) {
            return null;
        }
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $updatedPhpDocType);
        if (!$phpDocInfo->hasChanged()) {
            return null;
        }
        return $node;
    }
    private function isVarDocAlreadySet(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        foreach (['@var', '@phpstan-var', '@psalm-var'] as $tagName) {
            $varType = $phpDocInfo->getVarType($tagName);
            if (!$varType instanceof \PHPStan\Type\MixedType) {
                return \true;
            }
        }
        return \false;
    }
}
