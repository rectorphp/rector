<?php declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PHPUnit\NodeFactory\DataProviderClassMethodFactory;
use Rector\PHPUnit\ValueObject\DataProviderClassMethodRecipe;
use Rector\PHPUnit\ValueObject\ParamAndArgValueObject;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentInTestToDataProviderRector\ArrayArgumentInTestToDataProviderRectorTest
 */
final class ArrayArgumentInTestToDataProviderRector extends AbstractPHPUnitRector
{
    /**
     * @var mixed[]
     */
    private $configuration = [];

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var DataProviderClassMethodRecipe[]
     */
    private $dataProviderClassMethodRecipes = [];

    /**
     * @var DataProviderClassMethodFactory
     */
    private $dataProviderClassMethodFactory;

    /**
     * @param mixed[] $configuration
     */
    public function __construct(
        DocBlockManipulator $docBlockManipulator,
        StaticTypeMapper $staticTypeMapper,
        DataProviderClassMethodFactory $dataProviderClassMethodFactory,
        array $configuration = []
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->dataProviderClassMethodFactory = $dataProviderClassMethodFactory;
        $this->configuration = $configuration;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move array argument from tests into data provider [configurable]', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->doTestMultiple([1, 2, 3]);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(int $value)
    {
        $this->doTestSingle($value);
    }

    /**
     * @return int[]
     */
    public function provideDataForTest(): iterable
    {
        yield 1;
        yield 2;
        yield 3;
    }
}
CODE_SAMPLE

                ,
                [
                    '$configuration' => [
                        [
                            'class' => 'PHPUnit\Framework\TestCase',
                            'old_method' => 'doTestMultiple',
                            'new_method' => 'doTestSingle',
                        ],
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
        if (! $this->isInTestClass($node)) {
            return null;
        }

        $this->dataProviderClassMethodRecipes = [];

        $this->traverseNodesWithCallable($node->stmts, function (Node $node) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            foreach ($this->configuration as $singleConfiguration) {
                if (! $this->isMethodCallMatch($node, $singleConfiguration)) {
                    continue;
                }

                if (count($node->args) !== 1) {
                    throw new ShouldNotHappenException(__METHOD__);
                }

                // resolve value types
                $firstArgumentValue = $node->args[0]->value;
                if (! $firstArgumentValue instanceof Array_) {
                    throw new ShouldNotHappenException();
                }

                // rename method to new one handling non-array input
                $node->name = new Identifier($singleConfiguration['new_method']);

                $dataProviderMethodName = $this->createDataProviderMethodName($node);

                $this->dataProviderClassMethodRecipes[] = new DataProviderClassMethodRecipe(
                    $dataProviderMethodName,
                    $node->args,
                    $this->resolveUniqueArrayStaticType($firstArgumentValue)
                );

                $node->args = [];
                $paramAndArgs = $this->collectParamAndArgsFromArray($firstArgumentValue);
                foreach ($paramAndArgs as $paramAndArg) {
                    $node->args[] = new Arg($paramAndArg->getVariable());
                }

                /** @var ClassMethod $methodNode */
                $methodNode = $node->getAttribute(AttributeKey::METHOD_NODE);
                $this->refactorTestClassMethodParams($methodNode, $paramAndArgs);

                // add data provider annotation
                $dataProviderTagNode = $this->createDataProviderTagNode($dataProviderMethodName);
                $this->docBlockManipulator->addTag($methodNode, $dataProviderTagNode);

                return null;
            }

            return null;
        });

        if ($this->dataProviderClassMethodRecipes === []) {
            return null;
        }

        $dataProviderClassMethods = $this->createDataProviderClassMethodsFromRecipes();

        $node->stmts = array_Merge($node->stmts, $dataProviderClassMethods);

        return $node;
    }

    private function createDataProviderTagNode(string $dataProviderMethodName): PhpDocTagNode
    {
        return new PhpDocTagNode('@dataProvider', new GenericTagValueNode($dataProviderMethodName . '()'));
    }

    private function createParamTagNode(string $name, TypeNode $typeNode): PhpDocTagNode
    {
        return new PhpDocTagNode('@param', new ParamTagValueNode($typeNode, false, '$' . $name, ''));
    }

    private function resolveUniqueArrayStaticTypes(Array_ $array): ?Type
    {
        $itemStaticTypes = [];
        foreach ($array->items as $arrayItem) {
            $arrayItemStaticType = $this->getStaticType($arrayItem->value);
            if ($arrayItemStaticType instanceof MixedType) {
                continue;
            }

            $valueObjectHash = implode('_', $this->staticTypeMapper->mapPHPStanTypeToStrings($arrayItemStaticType));

            $itemStaticTypes[$valueObjectHash] = new ArrayType(new MixedType(), $arrayItemStaticType);
        }

        $itemStaticTypes = array_values($itemStaticTypes);

        if (count($itemStaticTypes) > 1) {
            return new UnionType($itemStaticTypes);
        }

        if (count($itemStaticTypes) === 1) {
            return $itemStaticTypes[0];
        }

        return null;
    }

    private function createDataProviderMethodName(Node $node): string
    {
        /** @var string $methodName */
        $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);

        return 'provideDataFor' . ucfirst($methodName);
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

    /**
     * @return ParamAndArgValueObject[]
     */
    private function collectParamAndArgsFromArray(Array_ $array): array
    {
        // multiple arguments
        $i = 1;

        $paramAndArgs = [];

        $isNestedArray = $this->isNestedArray($array);

        $itemsStaticType = $this->resolveItemStaticType($array, $isNestedArray);

        if ($isNestedArray === false) {
            foreach ($array->items as $arrayItem) {
                $variable = new Variable('variable' . ($i === 1 ? '' : $i));

                $paramAndArgs[] = new ParamAndArgValueObject($variable, $itemsStaticType);
                ++$i;

                if (! $arrayItem->value instanceof Array_) {
                    break;
                }
            }
        } else {
            foreach ($array->items as $arrayItem) {
                /** @var Array_ $nestedArray */
                $nestedArray = $arrayItem->value;
                foreach ($nestedArray->items as $nestedArrayItem) {
                    $variable = new Variable('variable' . ($i === 1 ? '' : $i));

                    $itemsStaticType = $this->getStaticType($nestedArrayItem->value);
                    $paramAndArgs[] = new ParamAndArgValueObject($variable, $itemsStaticType);
                    ++$i;
                }
            }
        }

        return $paramAndArgs;
    }

    /**
     * @param ParamAndArgValueObject[] $paramAndArgs
     * @return Param[]
     */
    private function createParams(array $paramAndArgs): array
    {
        $params = [];
        foreach ($paramAndArgs as $paramAndArg) {
            $param = new Param($paramAndArg->getVariable());

            $staticType = $paramAndArg->getType();

            if ($staticType !== null && ! $staticType instanceof UnionType) {
                $phpNodeType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($staticType);
                if ($phpNodeType !== null) {
                    $param->type = $phpNodeType;
                }
            }

            $params[] = $param;
        }

        return $params;
    }

    /**
     * @param Type[] $itemsStaticTypes
     * @return Type[]
     */
    private function filterUniqueStaticTypes(array $itemsStaticTypes): array
    {
        $uniqueStaticTypes = [];
        foreach ($itemsStaticTypes as $itemsStaticType) {
            $uniqueHash = implode('_', $this->staticTypeMapper->mapPHPStanTypeToStrings($itemsStaticType));
            $uniqueHash = md5($uniqueHash);

            $uniqueStaticTypes[$uniqueHash] = $itemsStaticType;
        }

        return array_values($uniqueStaticTypes);
    }

    private function resolveItemStaticType(Array_ $array, bool $isNestedArray): ?Type
    {
        $itemsStaticTypes = [];
        if ($isNestedArray === false) {
            foreach ($array->items as $arrayItem) {
                $arrayItemStaticType = $this->getStaticType($arrayItem->value);
                if ($arrayItemStaticType) {
                    $itemsStaticTypes[] = $arrayItemStaticType;
                }
            }
        }

        $itemsStaticTypes = $this->filterUniqueStaticTypes($itemsStaticTypes);

        if ($itemsStaticTypes !== null && count($itemsStaticTypes) > 1) {
            return new UnionType($itemsStaticTypes);
        }

        if (count($itemsStaticTypes) === 1) {
            return $itemsStaticTypes[0];
        }

        return null;
    }

    private function isNestedArray(Array_ $array): bool
    {
        foreach ($array->items as $arrayItem) {
            if ($arrayItem->value instanceof Array_) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $singleConfiguration
     */
    private function isMethodCallMatch(MethodCall $methodCall, array $singleConfiguration): bool
    {
        if (! $this->isObjectType($methodCall->var, $singleConfiguration['class'])) {
            return false;
        }

        return $this->isName($methodCall->name, $singleConfiguration['old_method']);
    }

    private function resolveUniqueArrayStaticType(Array_ $array): ?Type
    {
        $isNestedArray = $this->isNestedArray($array);

        $uniqueArrayStaticType = $this->resolveUniqueArrayStaticTypes($array);

        if ($isNestedArray && $uniqueArrayStaticType instanceof ArrayType) {
            // unwrap one level up
            return $uniqueArrayStaticType->getItemType();
        }

        return $uniqueArrayStaticType;
    }

    /**
     * @param ParamAndArgValueObject[] $paramAndArgs
     */
    private function refactorTestClassMethodParams(ClassMethod $classMethod, array $paramAndArgs): void
    {
        $classMethod->params = $this->createParams($paramAndArgs);

        foreach ($paramAndArgs as $paramAndArg) {
            $staticType = $paramAndArg->getType();

            if (! $staticType instanceof UnionType) {
                continue;
            }

            /** @var string $paramName */
            $paramName = $this->getName($paramAndArg->getVariable());

            /** @var TypeNode $staticTypeNode */
            $staticTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($staticType);

            $paramTagNode = $this->createParamTagNode($paramName, $staticTypeNode);
            $this->docBlockManipulator->addTag($classMethod, $paramTagNode);
        }
    }
}
