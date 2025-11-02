<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Class_\AddReturnArrayDocblockFromDataProviderParamRector\AddReturnArrayDocblockFromDataProviderParamRectorTest
 */
final class AddReturnArrayDocblockFromDataProviderParamRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
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
    private DocBlockUpdater $docBlockUpdater;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, StaticTypeMapper $staticTypeMapper, TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderMethodsFinder $dataProviderMethodsFinder, DocBlockUpdater $docBlockUpdater)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderMethodsFinder = $dataProviderMethodsFinder;
        $this->docBlockUpdater = $docBlockUpdater;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return array return from data provider param type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    /**
     * @dataProvider provideNames()
     */
    public function test(string $name)
    {
    }

    public function provideNames(): array
    {
        return ['John', 'Jane'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    /**
     * @dataProvider provideNames()
     */
    public function test(string $name)
    {
    }

    /**
     * @return string[]
     */
    public function provideNames(): array
    {
        return ['John', 'Jane'];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
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
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
                continue;
            }
            // sole param required
            if (count($classMethod->getParams()) !== 1) {
                continue;
            }
            $dataProviderNodes = $this->dataProviderMethodsFinder->findDataProviderNodes($node, $classMethod);
            $paramTypesByPosition = [];
            foreach ($classMethod->getParams() as $position => $param) {
                if (!$param->type instanceof Node) {
                    continue;
                }
                $paramTypesByPosition[$position] = $param->type;
            }
            if ($paramTypesByPosition === []) {
                continue;
            }
            foreach ($dataProviderNodes->getClassMethods() as $dataProviderClassMethod) {
                if (!$dataProviderClassMethod->returnType instanceof Identifier) {
                    continue;
                }
                if (!$this->isName($dataProviderClassMethod->returnType, 'array')) {
                    continue;
                }
                // already set return tag
                $dataProviderPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($dataProviderClassMethod);
                if ($dataProviderPhpDocInfo->getReturnTagValue() instanceof ReturnTagValueNode) {
                    continue;
                }
                $paramTypeNode = $paramTypesByPosition[0];
                $returnType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramTypeNode);
                $arrayReturnType = new ArrayType(new MixedType(), $returnType);
                $arrayReturnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($arrayReturnType);
                $returnTagValueNode = new ReturnTagValueNode($arrayReturnTypeNode, '');
                $dataProviderPhpDocInfo->addTagValueNode($returnTagValueNode);
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($dataProviderClassMethod);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
