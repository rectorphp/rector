<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\TypeAnalyzer\ParameterTypeFromDataProviderResolver;
use Rector\TypeDeclaration\ValueObject\DataProviderNodes;
use Rector\TypeDeclarationDocblocks\NodeFinder\DataProviderMethodsFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeBasedOnPHPUnitDataProviderRector\AddParamTypeBasedOnPHPUnitDataProviderRectorTest
 */
final class AddParamTypeBasedOnPHPUnitDataProviderRector extends AbstractRector
{
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
     * @var string
     */
    private const ERROR_MESSAGE = 'Adds param type declaration based on PHPUnit provider return type declaration';
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, DataProviderMethodsFinder $dataProviderMethodsFinder, ParameterTypeFromDataProviderResolver $parameterTypeFromDataProviderResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->dataProviderMethodsFinder = $dataProviderMethodsFinder;
        $this->parameterTypeFromDataProviderResolver = $parameterTypeFromDataProviderResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test($value)
    {
    }

    public static function provideData()
    {
        yield ['name'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $value)
    {
    }

    public static function provideData()
    {
        yield ['name'];
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
            if ($classMethod->getParams() === []) {
                continue;
            }
            $dataProviderNodes = $this->dataProviderMethodsFinder->findDataProviderNodes($node, $classMethod);
            if ($dataProviderNodes->getClassMethods() === []) {
                continue;
            }
            $hasClassMethodChanged = $this->refactorClassMethod($classMethod, $dataProviderNodes);
            if ($hasClassMethodChanged) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function refactorClassMethod(ClassMethod $classMethod, DataProviderNodes $dataProviderNodes): bool
    {
        $hasChanged = \false;
        foreach ($classMethod->getParams() as $parameterPosition => $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            if ($param->variadic) {
                continue;
            }
            $paramTypeDeclaration = $this->parameterTypeFromDataProviderResolver->resolve($parameterPosition, $dataProviderNodes->getClassMethods());
            if ($paramTypeDeclaration instanceof MixedType) {
                continue;
            }
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramTypeDeclaration, TypeKind::PARAM);
            if ($typeNode instanceof Node) {
                $param->type = $typeNode;
                $hasChanged = \true;
            }
        }
        return $hasChanged;
    }
}
