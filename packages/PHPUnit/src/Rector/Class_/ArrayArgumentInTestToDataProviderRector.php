<?php

declare(strict_types=1);

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
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPUnit\NodeFactory\DataProviderClassMethodFactory;
use Rector\PHPUnit\ValueObject\DataProviderClassMethodRecipe;
use Rector\PHPUnit\ValueObject\ParamAndArgValueObject;
use Rector\Rector\AbstractPHPUnitRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentInTestToDataProviderRector\ArrayArgumentInTestToDataProviderRectorTest
 *
 * @see why → https://blog.martinhujer.cz/how-to-use-data-providers-in-phpunit/
 */
final class ArrayArgumentInTestToDataProviderRector extends AbstractPHPUnitRector
{
    /**
     * @var mixed[]
     */
    private $configuration = [];

    /**
     * @var DataProviderClassMethodRecipe[]
     */
    private $dataProviderClassMethodRecipes = [];

    /**
     * @var DataProviderClassMethodFactory
     */
    private $dataProviderClassMethodFactory;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @param mixed[] $configuration
     */
    public function __construct(
        DataProviderClassMethodFactory $dataProviderClassMethodFactory,
        TypeFactory $typeFactory,
        array $configuration = []
    ) {
        $this->dataProviderClassMethodFactory = $dataProviderClassMethodFactory;
        $this->typeFactory = $typeFactory;
        $this->configuration = $configuration;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move array argument from tests into data provider [configurable]', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->doTestMultiple([1, 2, 3]);
    }
}
PHP
                ,
                <<<'PHP'
class SomeServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(int $number)
    {
        $this->doTestSingle($number);
    }

    public function provideDataForTest(): \Iterator
    {
        yield [1];
        yield [2];
        yield [3];
    }
}
PHP

                ,
                [
                    '$configuration' => [
                        [
                            'class' => 'PHPUnit\Framework\TestCase',
                            'old_method' => 'doTestMultiple',
                            'new_method' => 'doTestSingle',
                            'variable_name' => 'number',
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
        $this->ensureConfigurationIsSet($this->configuration);

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
                    throw new ShouldNotHappenException();
                }

                // resolve value types
                $firstArgumentValue = $node->args[0]->value;
                if (! $firstArgumentValue instanceof Array_) {
                    // nothing we can do
                    return null;
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
                $paramAndArgs = $this->collectParamAndArgsFromArray(
                    $firstArgumentValue,
                    $singleConfiguration['variable_name']
                );
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

    private function resolveUniqueArrayStaticTypes(Array_ $array): Type
    {
        $itemStaticTypes = [];
        foreach ($array->items as $arrayItem) {
            $arrayItemStaticType = $this->getStaticType($arrayItem->value);
            if ($arrayItemStaticType instanceof MixedType) {
                continue;
            }

            $itemStaticTypes[] = new ArrayType(new MixedType(), $arrayItemStaticType);
        }

        return $this->typeFactory->createMixedPassedOrUnionType($itemStaticTypes);
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
    private function collectParamAndArgsFromArray(Array_ $array, string $variableName): array
    {
        // multiple arguments
        $i = 1;

        $paramAndArgs = [];

        $isNestedArray = $this->isNestedArray($array);

        $itemsStaticType = $this->resolveItemStaticType($array, $isNestedArray);

        if ($isNestedArray === false) {
            foreach ($array->items as $arrayItem) {
                $variable = new Variable($variableName . ($i === 1 ? '' : $i));

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
                    $variable = new Variable($variableName . ($i === 1 ? '' : $i));

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

    private function resolveItemStaticType(Array_ $array, bool $isNestedArray): Type
    {
        $staticTypes = [];
        if ($isNestedArray === false) {
            foreach ($array->items as $arrayItem) {
                $arrayItemStaticType = $this->getStaticType($arrayItem->value);
                if ($arrayItemStaticType) {
                    $staticTypes[] = $arrayItemStaticType;
                }
            }
        }

        return $this->typeFactory->createMixedPassedOrUnionType($staticTypes);
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

    private function resolveUniqueArrayStaticType(Array_ $array): Type
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

    /**
     * @param mixed[] $configuration
     */
    private function ensureConfigurationIsSet(array $configuration): void
    {
        if ($configuration !== []) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Add configuration via "%s" argument for "%s"',
            '$configuration',
            self::class
        ));
    }
}
