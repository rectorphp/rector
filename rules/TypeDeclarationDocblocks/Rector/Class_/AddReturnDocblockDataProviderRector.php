<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder;
use Rector\TypeDeclarationDocblocks\NodeFinder\ReturnNodeFinder;
use Rector\TypeDeclarationDocblocks\NodeFinder\YieldNodeFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Rector\TypeDeclarationDocblocks\TypeResolver\YieldTypeResolver;
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
    private YieldTypeResolver $yieldTypeResolver;
    /**
     * @readonly
     */
    private YieldNodeFinder $yieldNodeFinder;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderMethodsFinder $dataProviderMethodsFinder, ReturnNodeFinder $returnNodeFinder, YieldTypeResolver $yieldTypeResolver, YieldNodeFinder $yieldNodeFinder, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderMethodsFinder = $dataProviderMethodsFinder;
        $this->returnNodeFinder = $returnNodeFinder;
        $this->yieldTypeResolver = $yieldTypeResolver;
        $this->yieldNodeFinder = $yieldNodeFinder;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
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
            if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($returnTagValueNode)) {
                continue;
            }
            $soleReturn = $this->returnNodeFinder->findOnlyReturnWithExpr($dataProviderClassMethod);
            // unable to resolve type
            if ($soleReturn instanceof Return_) {
                if (!$soleReturn->expr instanceof Expr) {
                    continue;
                }
                $soleReturnType = $this->getType($soleReturn->expr);
                $hasClassMethodChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableReturnType($soleReturnType, $classMethodPhpDocInfo, $dataProviderClassMethod);
                if (!$hasClassMethodChanged) {
                    continue;
                }
                $hasChanged = \true;
                continue;
            }
            $yields = $this->yieldNodeFinder->find($dataProviderClassMethod);
            if ($yields !== []) {
                $yieldType = $this->yieldTypeResolver->resolveFromYieldNodes($yields, $dataProviderClassMethod);
                if ($yieldType instanceof FullyQualifiedGenericObjectType && $yieldType->getClassName() === 'Generator' && !$dataProviderClassMethod->returnType instanceof Node) {
                    // most likely, a static iterator is used in data test fixtures
                    $yieldType = new FullyQualifiedGenericObjectType('Iterator', $yieldType->getTypes());
                }
                $hasClassMethodChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableReturnType($yieldType, $classMethodPhpDocInfo, $dataProviderClassMethod);
                if (!$hasClassMethodChanged) {
                    continue;
                }
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
