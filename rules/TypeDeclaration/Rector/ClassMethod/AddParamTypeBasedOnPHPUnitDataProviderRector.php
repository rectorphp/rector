<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use RectorPrefix202409\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\ValueObject\DataProviderNodes;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeBasedOnPHPUnitDataProviderRector\AddParamTypeBasedOnPHPUnitDataProviderRectorTest
 */
final class AddParamTypeBasedOnPHPUnitDataProviderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
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
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var string
     */
    private const ERROR_MESSAGE = 'Adds param type declaration based on PHPUnit provider return type declaration';
    /**
     * @see https://regex101.com/r/hW09Vt/1
     * @var string
     */
    private const METHOD_NAME_REGEX = '#^(?<method_name>\\w+)(\\(\\))?#';
    public function __construct(TypeFactory $typeFactory, TestsNodeAnalyzer $testsNodeAnalyzer, PhpDocInfoFactory $phpDocInfoFactory, BetterNodeFinder $betterNodeFinder, StaticTypeMapper $staticTypeMapper)
    {
        $this->typeFactory = $typeFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
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
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($classMethod->getParams() === []) {
                continue;
            }
            $dataProviderNodes = $this->resolveDataProviderNodes($classMethod);
            if ($dataProviderNodes->isEmpty()) {
                return null;
            }
            $hasClassMethodChanged = $this->refactorClassMethod($classMethod, $node, $dataProviderNodes->nodes);
            if ($hasClassMethodChanged) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode|\PhpParser\Node\Attribute $dataProviderNode
     */
    private function inferParam(Class_ $class, int $parameterPosition, $dataProviderNode) : Type
    {
        $dataProviderClassMethod = $this->resolveDataProviderClassMethod($class, $dataProviderNode);
        if (!$dataProviderClassMethod instanceof ClassMethod) {
            return new MixedType();
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($dataProviderClassMethod);
        if ($returns !== []) {
            return $this->resolveReturnStaticArrayTypeByParameterPosition($returns, $parameterPosition);
        }
        /** @var Yield_[] $yields */
        $yields = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($dataProviderClassMethod, Yield_::class);
        return $this->resolveYieldStaticArrayTypeByParameterPosition($yields, $parameterPosition);
    }
    /**
     * @param \PhpParser\Node\Attribute|\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $dataProviderNode
     */
    private function resolveDataProviderClassMethod(Class_ $class, $dataProviderNode) : ?ClassMethod
    {
        if ($dataProviderNode instanceof Attribute) {
            $value = $dataProviderNode->args[0]->value;
            if (!$value instanceof String_) {
                return null;
            }
            $content = $value->value;
        } elseif ($dataProviderNode->value instanceof GenericTagValueNode) {
            $content = $dataProviderNode->value->value;
        } else {
            return null;
        }
        $match = Strings::match($content, self::METHOD_NAME_REGEX);
        if ($match === null) {
            return null;
        }
        $methodName = $match['method_name'];
        return $class->getMethod($methodName);
    }
    /**
     * @param Return_[] $returns
     */
    private function resolveReturnStaticArrayTypeByParameterPosition(array $returns, int $parameterPosition) : Type
    {
        $firstReturnedExpr = $returns[0]->expr;
        if (!$firstReturnedExpr instanceof Array_) {
            return new MixedType();
        }
        $paramOnPositionTypes = $this->resolveParamOnPositionTypes($firstReturnedExpr, $parameterPosition);
        if ($paramOnPositionTypes === []) {
            return new MixedType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($paramOnPositionTypes);
    }
    /**
     * @param Yield_[] $yields
     */
    private function resolveYieldStaticArrayTypeByParameterPosition(array $yields, int $parameterPosition) : Type
    {
        $paramOnPositionTypes = [];
        foreach ($yields as $yield) {
            if (!$yield->value instanceof Array_) {
                continue;
            }
            $type = $this->getTypeFromClassMethodYield($yield->value);
            if (!$type instanceof ConstantArrayType) {
                return $type;
            }
            foreach ($type->getValueTypes() as $position => $valueType) {
                if ($position !== $parameterPosition) {
                    continue;
                }
                $paramOnPositionTypes[] = $valueType;
            }
        }
        if ($paramOnPositionTypes === []) {
            return new MixedType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($paramOnPositionTypes);
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\Constant\ConstantArrayType
     */
    private function getTypeFromClassMethodYield(Array_ $classMethodYieldArray)
    {
        $arrayType = $this->nodeTypeResolver->getType($classMethodYieldArray);
        // impossible to resolve
        if (!$arrayType instanceof ConstantArrayType) {
            return new MixedType();
        }
        return $arrayType;
    }
    /**
     * @return Type[]
     */
    private function resolveParamOnPositionTypes(Array_ $array, int $parameterPosition) : array
    {
        $paramOnPositionTypes = [];
        foreach ($array->items as $singleDataProvidedSet) {
            if (!$singleDataProvidedSet instanceof ArrayItem || !$singleDataProvidedSet->value instanceof Array_) {
                throw new ShouldNotHappenException();
            }
            foreach ($singleDataProvidedSet->value->items as $position => $singleDataProvidedSetItem) {
                if ($position !== $parameterPosition) {
                    continue;
                }
                if (!$singleDataProvidedSetItem instanceof ArrayItem) {
                    continue;
                }
                $paramOnPositionTypes[] = $this->nodeTypeResolver->getType($singleDataProvidedSetItem->value);
            }
        }
        return $paramOnPositionTypes;
    }
    private function resolveDataProviderNodes(ClassMethod $classMethod) : DataProviderNodes
    {
        $attributes = $this->getPhpDataProviderAttributes($classMethod);
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        $phpdocNodes = $classMethodPhpDocInfo instanceof PhpDocInfo ? $classMethodPhpDocInfo->getTagsByName('@dataProvider') : [];
        return new DataProviderNodes(\array_merge($attributes, $phpdocNodes));
    }
    /**
     * @return array<array-key, Attribute>
     */
    private function getPhpDataProviderAttributes(ClassMethod $classMethod) : array
    {
        $attributeName = 'PHPUnit\\Framework\\Attributes\\DataProvider';
        /** @var AttributeGroup[] $attrGroups */
        $attrGroups = $classMethod->attrGroups;
        $dataProviders = [];
        foreach ($attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->nodeNameResolver->isName($attribute->name, $attributeName)) {
                    continue;
                }
                $dataProviders[] = $attribute;
            }
        }
        return $dataProviders;
    }
    /**
     * @param array<Attribute|PhpDocTagNode> $dataProviderNodes
     */
    private function refactorClassMethod(ClassMethod $classMethod, Class_ $class, array $dataProviderNodes) : bool
    {
        $hasChanged = \false;
        foreach ($classMethod->getParams() as $parameterPosition => $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            if ($param->variadic) {
                continue;
            }
            $paramTypes = [];
            foreach ($dataProviderNodes as $dataProviderNode) {
                $paramTypes[] = $this->inferParam($class, $parameterPosition, $dataProviderNode);
            }
            $paramTypeDeclaration = TypeCombinator::union(...$paramTypes);
            if ($paramTypeDeclaration instanceof MixedType) {
                continue;
            }
            $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramTypeDeclaration, TypeKind::PARAM);
            $hasChanged = \true;
        }
        return $hasChanged;
    }
}
