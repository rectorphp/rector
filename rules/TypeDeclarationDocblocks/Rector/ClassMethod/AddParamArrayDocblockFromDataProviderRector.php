<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Privatization\TypeManipulator\TypeNormalizer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeAnalyzer\ParameterTypeFromDataProviderResolver;
use Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder;
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
    private DocBlockUpdater $docBlockUpdater;
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
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private TypeNormalizer $typeNormalizer;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, DocBlockUpdater $docBlockUpdater, TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderMethodsFinder $dataProviderMethodsFinder, ParameterTypeFromDataProviderResolver $parameterTypeFromDataProviderResolver, StaticTypeMapper $staticTypeMapper, TypeNormalizer $typeNormalizer)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderMethodsFinder = $dataProviderMethodsFinder;
        $this->parameterTypeFromDataProviderResolver = $parameterTypeFromDataProviderResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeNormalizer = $typeNormalizer;
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
                if (!$this->isName($param->type, 'array')) {
                    continue;
                }
                /** @var string $paramName */
                $paramName = $this->getName($param->var);
                $paramTagValueNode = $phpDocInfo->getParamTagValueByName($paramName);
                // already defined, lets skip it
                if ($paramTagValueNode instanceof ParamTagValueNode) {
                    continue;
                }
                $parameterType = $this->parameterTypeFromDataProviderResolver->resolve($paramPosition, $dataProviderNodes->getClassMethods());
                // skip mixed type, as it is not informative
                if ($parameterType instanceof ArrayType && $parameterType->getItemType() instanceof MixedType) {
                    continue;
                }
                $generalizedParameterType = $this->typeNormalizer->generalizeConstantTypes($parameterType);
                $parameterTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($generalizedParameterType);
                $paramTagValueNode = new ParamTagValueNode($parameterTypeNode, \false, '$' . $paramName, '', \false);
                $phpDocInfo->addTagValueNode($paramTagValueNode);
                $hasChanged = \true;
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
