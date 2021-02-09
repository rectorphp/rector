<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\DataProviderClassMethodFactory;
use Rector\PHPUnit\NodeManipulator\ParamAndArgFromArrayResolver;
use Rector\PHPUnit\ValueObject\ArrayArgumentToDataProvider;
use Rector\PHPUnit\ValueObject\DataProviderClassMethodRecipe;
use Rector\PHPUnit\ValueObject\ParamAndArg;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentToDataProviderRector\ArrayArgumentToDataProviderRectorTest
 *
 * @see why → https://blog.martinhujer.cz/how-to-use-data-providers-in-phpunit/
 */
final class ArrayArgumentToDataProviderRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const ARRAY_ARGUMENTS_TO_DATA_PROVIDERS = 'array_arguments_to_data_providers';

    /**
     * @var ArrayArgumentToDataProvider[]
     */
    private $arrayArgumentsToDataProviders = [];

    /**
     * @var DataProviderClassMethodRecipe[]
     */
    private $dataProviderClassMethodRecipes = [];

    /**
     * @var DataProviderClassMethodFactory
     */
    private $dataProviderClassMethodFactory;

    /**
     * @var ParamAndArgFromArrayResolver
     */
    private $paramAndArgFromArrayResolver;

    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    public function __construct(
        DataProviderClassMethodFactory $dataProviderClassMethodFactory,
        ParamAndArgFromArrayResolver $paramAndArgFromArrayResolver,
        TestsNodeAnalyzer $testsNodeAnalyzer
    ) {
        $this->dataProviderClassMethodFactory = $dataProviderClassMethodFactory;
        $this->paramAndArgFromArrayResolver = $paramAndArgFromArrayResolver;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move array argument from tests into data provider [configurable]', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
    public function test()
    {
        $this->doTestMultiple([1, 2, 3]);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

class SomeServiceTest extends TestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(int $number)
    {
        $this->doTestSingle($number);
    }

    public function provideData(): \Iterator
    {
        yield [1];
        yield [2];
        yield [3];
    }
}
CODE_SAMPLE

                ,
                [
                    self::ARRAY_ARGUMENTS_TO_DATA_PROVIDERS => [
                        new ArrayArgumentToDataProvider(
                            'PHPUnit\Framework\TestCase',
                            'doTestMultiple',
                            'doTestSingle',
                            'number'
                        ),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
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
        if (! $this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }

        $this->dataProviderClassMethodRecipes = [];

        $this->traverseNodesWithCallable($node->stmts, function (Node $node) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            foreach ($this->arrayArgumentsToDataProviders as $arrayArgumentsToDataProvider) {
                $this->refactorMethodCallWithConfiguration($node, $arrayArgumentsToDataProvider);
            }

            return null;
        });

        if ($this->dataProviderClassMethodRecipes === []) {
            return null;
        }

        $dataProviderClassMethods = $this->createDataProviderClassMethodsFromRecipes();

        $node->stmts = array_merge($node->stmts, $dataProviderClassMethods);

        return $node;
    }

    public function configure(array $arrayArgumentsToDataProviders): void
    {
        $arrayArgumentsToDataProviders = $arrayArgumentsToDataProviders[self::ARRAY_ARGUMENTS_TO_DATA_PROVIDERS] ?? [];
        Assert::allIsInstanceOf($arrayArgumentsToDataProviders, ArrayArgumentToDataProvider::class);
        $this->arrayArgumentsToDataProviders = $arrayArgumentsToDataProviders;
    }

    private function refactorMethodCallWithConfiguration(
        MethodCall $methodCall,
        ArrayArgumentToDataProvider $arrayArgumentToDataProvider
    ): void {
        if (! $this->isMethodCallMatch($methodCall, $arrayArgumentToDataProvider)) {
            return;
        }

        if (count($methodCall->args) !== 1) {
            throw new ShouldNotHappenException();
        }

        // resolve value types
        $firstArgumentValue = $methodCall->args[0]->value;
        if (! $firstArgumentValue instanceof Array_) {
            // nothing we can do
            return;
        }

        // rename method to new one handling non-array input
        $methodCall->name = new Identifier($arrayArgumentToDataProvider->getNewMethod());

        $dataProviderMethodName = $this->createDataProviderMethodName($methodCall);

        $this->dataProviderClassMethodRecipes[] = new DataProviderClassMethodRecipe(
            $dataProviderMethodName,
            $methodCall->args
        );

        $methodCall->args = [];

        $paramAndArgs = $this->paramAndArgFromArrayResolver->resolve(
            $firstArgumentValue,
            $arrayArgumentToDataProvider->getVariableName()
        );

        foreach ($paramAndArgs as $paramAndArg) {
            $methodCall->args[] = new Arg($paramAndArg->getVariable());
        }

        /** @var ClassMethod $classMethod */
        $classMethod = $methodCall->getAttribute(AttributeKey::METHOD_NODE);

        $this->refactorTestClassMethodParams($classMethod, $paramAndArgs);

        // add data provider annotation
        $dataProviderTagNode = $this->createDataProviderTagNode($dataProviderMethodName);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $phpDocInfo->addPhpDocTagNode($dataProviderTagNode);
    }

    /**
     * @return ClassMethod[]
     */
    private function createDataProviderClassMethodsFromRecipes(): array
    {
        $dataProviderClassMethods = [];

        foreach ($this->dataProviderClassMethodRecipes as $dataProviderClassMethodRecipe) {
            $dataProviderClassMethods[] = $this->dataProviderClassMethodFactory->createFromRecipe(
                $dataProviderClassMethodRecipe
            );
        }

        return $dataProviderClassMethods;
    }

    private function isMethodCallMatch(
        MethodCall $methodCall,
        ArrayArgumentToDataProvider $arrayArgumentToDataProvider
    ): bool {
        if (! $this->isObjectType($methodCall->var, $arrayArgumentToDataProvider->getClass())) {
            return false;
        }

        return $this->isName($methodCall->name, $arrayArgumentToDataProvider->getOldMethod());
    }

    private function createDataProviderMethodName(MethodCall $methodCall): string
    {
        /** @var string $methodName */
        $methodName = $methodCall->getAttribute(AttributeKey::METHOD_NAME);

        return 'provideDataFor' . ucfirst($methodName);
    }

    /**
     * @param ParamAndArg[] $paramAndArgs
     */
    private function refactorTestClassMethodParams(ClassMethod $classMethod, array $paramAndArgs): void
    {
        $classMethod->params = $this->createParams($paramAndArgs);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        foreach ($paramAndArgs as $paramAndArg) {
            $staticType = $paramAndArg->getType();

            if (! $staticType instanceof UnionType) {
                continue;
            }

            /** @var string $paramName */
            $paramName = $this->getName($paramAndArg->getVariable());

            /** @var TypeNode $staticTypeNode */
            $staticTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($staticType);

            $paramTagValueNode = $this->createParamTagNode($paramName, $staticTypeNode);
            $phpDocInfo->addTagValueNode($paramTagValueNode);
        }
    }

    private function createDataProviderTagNode(string $dataProviderMethodName): PhpDocTagNode
    {
        return new AttributeAwarePhpDocTagNode('@dataProvider', new GenericTagValueNode(
            $dataProviderMethodName . '()'
        ));
    }

    /**
     * @param ParamAndArg[] $paramAndArgs
     * @return Param[]
     */
    private function createParams(array $paramAndArgs): array
    {
        $params = [];
        foreach ($paramAndArgs as $paramAndArg) {
            $param = new Param($paramAndArg->getVariable());
            $this->setTypeIfNotNull($paramAndArg, $param);

            $params[] = $param;
        }

        return $params;
    }

    private function createParamTagNode(string $name, TypeNode $typeNode): AttributeAwareParamTagValueNode
    {
        return new AttributeAwareParamTagValueNode($typeNode, false, '$' . $name, '');
    }

    private function setTypeIfNotNull(ParamAndArg $paramAndArg, Param $param): void
    {
        $staticType = $paramAndArg->getType();
        if (! $staticType instanceof Type) {
            return;
        }

        if ($staticType instanceof UnionType) {
            return;
        }

        $phpNodeType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($staticType);
        if ($phpNodeType === null) {
            return;
        }

        $param->type = $phpNodeType;
    }
}
