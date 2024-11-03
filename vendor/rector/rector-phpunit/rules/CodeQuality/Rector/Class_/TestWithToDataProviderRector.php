<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use RectorPrefix202411\Nette\Utils\Json;
use RectorPrefix202411\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeManipulator\ClassInsertManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\TestWithToDataProviderRector\TestWithToDataProviderRectorTest
 */
final class TestWithToDataProviderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, ClassInsertManipulator $classInsertManipulator)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->classInsertManipulator = $classInsertManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace testWith annotation to data provider.', [new CodeSample(<<<'CODE_SAMPLE'
/**
 * @testWith    [0, 0, 0]
 * @testWith    [0, 1, 1]
 * @testWith    [1, 0, 1]
 * @testWith    [1, 1, 3]
 */
public function testSum(int $a, int $b, int $expected)
{
    $this->assertSame($expected, $a + $b);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
public function dataProviderSum()
{
    return [
        [0, 0, 0],
        [0, 1, 1],
        [1, 0, 1],
        [1, 1, 3]
    ];
}

/**
 * @dataProvider dataProviderSum
 */
public function test(int $a, int $b, int $expected)
{
    $this->assertSame($expected, $a + $b);
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $this->hasChanged = \false;
        foreach ($node->stmts as $classMethod) {
            if (!$classMethod instanceof ClassMethod) {
                continue;
            }
            $this->refactorClassMethod($node, $classMethod);
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    private function refactorClassMethod(Class_ $class, ClassMethod $classMethod) : void
    {
        $arrayItemsSingleLine = [];
        $arrayMultiLine = null;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $testWithPhpDocTagNodes = \array_merge($phpDocInfo->getTagsByName('testWith'), $phpDocInfo->getTagsByName('testwith'));
        if ($testWithPhpDocTagNodes === []) {
            return;
        }
        foreach ($testWithPhpDocTagNodes as $testWithPhpDocTagNode) {
            if (!$testWithPhpDocTagNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $values = $this->extractTestWithData($testWithPhpDocTagNode->value);
            if (\count($values) > 1) {
                $arrayMultiLine = $this->createArrayItem($values);
            }
            if (\count($values) === 1) {
                $arrayItemsSingleLine[] = new ArrayItem($this->createArrayItem($values[0]));
            }
            //cleanup
            if ($this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $testWithPhpDocTagNode)) {
                $this->hasChanged = \true;
            }
        }
        if (!$this->hasChanged) {
            return;
        }
        $dataProviderName = $this->generateDataProviderName($classMethod);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@dataProvider', new GenericTagValueNode($dataProviderName)));
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
        $returnValue = $arrayMultiLine;
        if ($arrayItemsSingleLine !== []) {
            $returnValue = new Array_($arrayItemsSingleLine);
        }
        $providerMethod = new ClassMethod($dataProviderName);
        $providerMethod->flags = Class_::MODIFIER_PUBLIC;
        $providerMethod->stmts[] = new Return_($returnValue);
        $this->classInsertManipulator->addAsFirstMethod($class, $providerMethod);
    }
    /**
     * @return array<array<int, mixed>>
     */
    private function extractTestWithData(GenericTagValueNode $genericTagValueNode) : array
    {
        $testWithItems = \explode("\n", \trim($genericTagValueNode->value));
        $jsonArray = [];
        foreach ($testWithItems as $testWithItem) {
            $jsonArray[] = Json::decode(\trim($testWithItem), Json::FORCE_ARRAY);
        }
        return $jsonArray;
    }
    /**
     * @param array<int|string, mixed> $data
     */
    private function createArrayItem(array $data) : Array_
    {
        $values = [];
        foreach ($data as $index => $item) {
            $key = null;
            if (\is_string($index)) {
                $key = new String_($index);
            }
            $values[] = new ArrayItem($this->parseArrayItemValue($item), $key);
        }
        return new Array_($values);
    }
    /**
     * @param mixed $value
     */
    private function parseArrayItemValue($value) : Expr
    {
        if (\is_array($value)) {
            return $this->createArrayItem($value);
        }
        $name = new Name(Json::encode($value));
        return new ConstFetch($name);
    }
    private function generateDataProviderName(ClassMethod $classMethod) : string
    {
        $methodName = Strings::replace($classMethod->name->name, '/^test/', '');
        $upperCaseFirstLatter = \ucfirst($methodName);
        return \sprintf('%s%s', 'dataProvider', $upperCaseFirstLatter);
    }
}
