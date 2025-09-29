<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\Constant\ConstantArrayType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Rector\TypeDeclarationDocblocks\TypeResolver\ConstantArrayTypeGeneralizer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockReturnArrayFromDirectArrayInstanceRector\DocblockReturnArrayFromDirectArrayInstanceRectorTest
 */
final class DocblockReturnArrayFromDirectArrayInstanceRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private ConstantArrayTypeGeneralizer $constantArrayTypeGeneralizer;
    /**
     * @readonly
     */
    private ReturnNodeFinder $returnNodeFinder;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, ConstantArrayTypeGeneralizer $constantArrayTypeGeneralizer, ReturnNodeFinder $returnNodeFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->constantArrayTypeGeneralizer = $constantArrayTypeGeneralizer;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add simple @return array docblock based on direct single level direct return of []', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function getItems(): array
    {
        return [
            'hey' => 'now',
        ];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return array<string, string>
     */
    public function getItems(): array
    {
        return [
            'hey' => 'now',
        ];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($phpDocInfo->getReturnTagValue())) {
            return null;
        }
        $soleReturn = $this->returnNodeFinder->findOnlyReturnWithExpr($node);
        if (!$soleReturn instanceof Return_) {
            return null;
        }
        if (!$soleReturn->expr instanceof Array_) {
            return null;
        }
        // resolve simple type
        $returnedType = $this->getType($soleReturn->expr);
        if (!$returnedType instanceof ConstantArrayType) {
            return null;
        }
        if ($returnedType->getReferencedClasses() !== []) {
            // better handled by shared-interface/class rule, to avoid turning objects to mixed
            return null;
        }
        $genericTypeNode = $this->constantArrayTypeGeneralizer->generalize($returnedType);
        $this->phpDocTypeChanger->changeReturnTypeNode($node, $phpDocInfo, $genericTypeNode);
        return $node;
    }
}
