<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\TypeDeclaration\Guard\PhpDocNestedAnnotationGuard;
use Rector\TypeDeclaration\Helper\PhpDocNullableTypeHelper;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnAnnotationIncorrectNullableRector\ReturnAnnotationIncorrectNullableRectorTest
 */
final class ReturnAnnotationIncorrectNullableRector extends AbstractRector
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
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\Guard\PhpDocNestedAnnotationGuard
     */
    private $phpDocNestedAnnotationGuard;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, PhpDocNullableTypeHelper $phpDocNullableTypeHelper, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, PhpDocNestedAnnotationGuard $phpDocNestedAnnotationGuard)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->phpDocNullableTypeHelper = $phpDocNullableTypeHelper;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->phpDocNestedAnnotationGuard = $phpDocNestedAnnotationGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add or remove null type from @return phpdoc typehint based on php return type declaration', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return \DateTime[]
     */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return \DateTime[]|null
     */
    public function getDateTimes(): ?array
    {
        return $this->dateTimes;
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
        $returnType = $node->getReturnType();
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node)) {
            return null;
        }
        if ($returnType === null) {
            return null;
        }
        if (!$this->phpDocNestedAnnotationGuard->isPhpDocCommentCorrectlyParsed($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return null;
        }
        $phpStanDocTypeNode = $returnTagValueNode->type;
        $phpParserType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($returnType);
        $docType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($phpStanDocTypeNode, $node);
        $updatedPhpDocType = $this->phpDocNullableTypeHelper->resolveUpdatedPhpDocTypeFromPhpDocTypeAndPhpParserType($docType, $phpParserType);
        if (!$updatedPhpDocType instanceof Type) {
            return null;
        }
        $hasReturnTypeChanged = $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $updatedPhpDocType);
        if ($hasReturnTypeChanged) {
            return $node;
        }
        return null;
    }
}
