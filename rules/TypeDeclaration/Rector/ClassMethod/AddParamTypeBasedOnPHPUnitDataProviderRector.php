<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use RectorPrefix202308\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
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
     * @var string
     */
    private const ERROR_MESSAGE = 'Adds param type declaration based on PHPUnit provider return type declaration';
    /**
     * @see https://regex101.com/r/hW09Vt/1
     * @var string
     */
    private const METHOD_NAME_REGEX = '#^(?<method_name>\\w+)(\\(\\))?#';
    public function __construct(TypeFactory $typeFactory, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->typeFactory = $typeFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition(self::ERROR_MESSAGE, [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase

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
use PHPUnit\Framework\TestCase

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
            $dataProviderPhpDocTagNode = $this->resolveDataProviderPhpDocTagNode($classMethod);
            if (!$dataProviderPhpDocTagNode instanceof PhpDocTagNode) {
                return null;
            }
            $hasClassMethodChanged = $this->refactorClassMethod($classMethod, $node, $dataProviderPhpDocTagNode);
            if ($hasClassMethodChanged) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function inferParam(Class_ $class, Param $param, PhpDocTagNode $dataProviderPhpDocTagNode) : Type
    {
        $dataProviderClassMethod = $this->resolveDataProviderClassMethod($class, $dataProviderPhpDocTagNode);
        if (!$dataProviderClassMethod instanceof ClassMethod) {
            return new MixedType();
        }
        $parameterPosition = $param->getAttribute(AttributeKey::PARAMETER_POSITION);
        if ($parameterPosition === null) {
            return new MixedType();
        }
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf((array) $dataProviderClassMethod->stmts, Return_::class);
        if ($returns !== []) {
            return $this->resolveReturnStaticArrayTypeByParameterPosition($returns, $parameterPosition);
        }
        /** @var Yield_[] $yields */
        $yields = $this->betterNodeFinder->findInstanceOf((array) $dataProviderClassMethod->stmts, Yield_::class);
        return $this->resolveYieldStaticArrayTypeByParameterPosition($yields, $parameterPosition);
    }
    private function resolveDataProviderClassMethod(Class_ $class, PhpDocTagNode $dataProviderPhpDocTagNode) : ?ClassMethod
    {
        if (!$dataProviderPhpDocTagNode->value instanceof GenericTagValueNode) {
            return null;
        }
        $content = $dataProviderPhpDocTagNode->value->value;
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
    private function getTypeFromClassMethodYield(Array_ $classMethodYieldArrayNode)
    {
        $arrayType = $this->nodeTypeResolver->getType($classMethodYieldArrayNode);
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
    private function resolveDataProviderPhpDocTagNode(ClassMethod $classMethod) : ?PhpDocTagNode
    {
        $classMethodPhpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$classMethodPhpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        return $classMethodPhpDocInfo->getByName('@dataProvider');
    }
    private function refactorClassMethod(ClassMethod $classMethod, Class_ $class, PhpDocTagNode $dataProviderPhpDocTagNode) : bool
    {
        $hasChanged = \false;
        foreach ($classMethod->getParams() as $param) {
            if ($param->type instanceof Node) {
                continue;
            }
            $paramTypeDeclaration = $this->inferParam($class, $param, $dataProviderPhpDocTagNode);
            if ($paramTypeDeclaration instanceof MixedType) {
                continue;
            }
            $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramTypeDeclaration, TypeKind::PARAM);
            $hasChanged = \true;
        }
        return $hasChanged;
    }
}
