<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\TypeAnalyzer\ParameterTypeFromDataProviderResolver;
use Rector\TypeDeclarationDocblocks\NodeDocblockTypeDecorator;
use Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder;
use Rector\TypeDeclarationDocblocks\TagNodeAnalyzer\UsefulArrayTagNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDataProviderRector\AddParamArrayDocblockFromDataProviderRectorTest
 */
final class AddParamArrayDocblockFromDataProviderRector extends AbstractRector
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
    private ParameterTypeFromDataProviderResolver $parameterTypeFromDataProviderResolver;
    /**
     * @readonly
     */
    private UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer;
    /**
     * @readonly
     */
    private NodeDocblockTypeDecorator $nodeDocblockTypeDecorator;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderMethodsFinder $dataProviderMethodsFinder, ParameterTypeFromDataProviderResolver $parameterTypeFromDataProviderResolver, UsefulArrayTagNodeAnalyzer $usefulArrayTagNodeAnalyzer, NodeDocblockTypeDecorator $nodeDocblockTypeDecorator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderMethodsFinder = $dataProviderMethodsFinder;
        $this->parameterTypeFromDataProviderResolver = $parameterTypeFromDataProviderResolver;
        $this->usefulArrayTagNodeAnalyzer = $usefulArrayTagNodeAnalyzer;
        $this->nodeDocblockTypeDecorator = $nodeDocblockTypeDecorator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param docblock array type, based on data provider data type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SomeTest extends TestCase
{
    #[DataProvider('provideData')]
    public function test(array $names): void
    {
    }

    public static function provideData()
    {
        yield [['Tom', 'John']];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SomeTest extends TestCase
{
    /**
     * @param string[] $names
     */
    #[DataProvider('provideData')]
    public function test(array $names): void
    {
    }

    public static function provideData()
    {
        yield [['Tom', 'John']];
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
            if ($classMethod->getParams() === []) {
                continue;
            }
            if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $dataProviderNodes = $this->dataProviderMethodsFinder->findDataProviderNodes($node, $classMethod);
            if ($dataProviderNodes->getClassMethods() === []) {
                continue;
            }
            foreach ($classMethod->getParams() as $paramPosition => $param) {
                // we are interested only in array params
                if (!$param->type instanceof Node) {
                    continue;
                }
                if (!$this->isNames($param->type, ['array', 'iterable'])) {
                    continue;
                }
                /** @var string $paramName */
                $paramName = $this->getName($param->var);
                $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
                // already defined, lets skip it
                if ($this->usefulArrayTagNodeAnalyzer->isUsefulArrayTag($paramTagValueNode)) {
                    continue;
                }
                $parameterType = $this->parameterTypeFromDataProviderResolver->resolve($paramPosition, $dataProviderNodes->getClassMethods());
                $hasParamTypeChanged = $this->nodeDocblockTypeDecorator->decorateGenericIterableParamType($parameterType, $phpDocInfo, $classMethod, $param, $paramName);
                if (!$hasParamTypeChanged) {
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
