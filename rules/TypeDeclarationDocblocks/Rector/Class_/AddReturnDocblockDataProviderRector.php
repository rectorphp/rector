<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Privatization\TypeManipulator\TypeNormalizer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder;
use Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\AddReturnDocblockDataProviderRector\AddReturnDocblockDataProviderRectorTest
 */
final class AddReturnDocblockDataProviderRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private DataProviderMethodsFinder $dataProviderMethodsFinder;
    /**
     * @readonly
     */
    private ReturnNodeFinder $returnNodeFinder;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private TypeNormalizer $typeNormalizer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderMethodsFinder $dataProviderMethodsFinder, ReturnNodeFinder $returnNodeFinder, StaticTypeMapper $staticTypeMapper, DocBlockUpdater $docBlockUpdater, TypeNormalizer $typeNormalizer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderMethodsFinder = $dataProviderMethodsFinder;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->typeNormalizer = $typeNormalizer;
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return array docblock to array provider method', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @dataProvider provideItems()
     */
    public function testSomething(array $items)
    {
    }

    public function provideItems()
    {
        return [
            [['item1', 'item2']],
            [['item3', 'item4']],
        ];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @dataProvider provideItems()
     */
    public function testSomething(array $items)
    {
    }

    /**
     * @return array<array<string>>
     */
    public function provideItems()
    {
        return [
            [['item1', 'item2']],
            [['item3', 'item4']],
        ];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        $dataProviderClassMethods = $this->dataProviderMethodsFinder->findDataProviderNodesInClass($node);
        if ($dataProviderClassMethods === []) {
            return null;
        }
        foreach ($dataProviderClassMethods as $dataProviderClassMethod) {
            $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($dataProviderClassMethod);
            $returnTagValueNode = $classMethodPhpDocInfo->getReturnTagValue();
            // already set
            if ($returnTagValueNode instanceof ReturnTagValueNode) {
                continue;
            }
            $soleReturn = $this->returnNodeFinder->findOnlyReturnWithExpr($dataProviderClassMethod);
            // unable to resolve type
            if (!$soleReturn instanceof Return_) {
                continue;
            }
            if (!$soleReturn->expr instanceof Expr) {
                continue;
            }
            $soleReturnType = $this->getType($soleReturn->expr);
            $generalizedReturnType = $this->typeNormalizer->generalizeConstantBoolTypes($soleReturnType);
            // turn into rather generic short return type, to keep it open to extension later and readable to human
            $docType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($generalizedReturnType);
            $returnTagValueNode = new ReturnTagValueNode($docType, '');
            $classMethodPhpDocInfo->addTagValueNode($returnTagValueNode);
            $hasChanged = \true;
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($dataProviderClassMethod);
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
