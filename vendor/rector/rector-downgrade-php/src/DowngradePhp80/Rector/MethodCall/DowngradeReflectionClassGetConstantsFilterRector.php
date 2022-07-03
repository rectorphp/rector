<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeCollector\BinaryOpConditionsCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\DowngradeReflectionClassGetConstantsFilterRectorTest
 */
final class DowngradeReflectionClassGetConstantsFilterRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const MAP_CONSTANT_TO_METHOD = ['IS_PUBLIC' => 'isPublic', 'IS_PROTECTED' => 'isProtected', 'IS_PRIVATE' => 'isPrivate'];
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\NodeCollector\BinaryOpConditionsCollector
     */
    private $binaryOpConditionsCollector;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(VariableNaming $variableNaming, IfManipulator $ifManipulator, BinaryOpConditionsCollector $binaryOpConditionsCollector, NodesToAddCollector $nodesToAddCollector)
    {
        $this->variableNaming = $variableNaming;
        $this->ifManipulator = $ifManipulator;
        $this->binaryOpConditionsCollector = $binaryOpConditionsCollector;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade ReflectionClass->getConstants(ReflectionClassConstant::IS_*)', [new CodeSample(<<<'CODE_SAMPLE'
$reflectionClass = new ReflectionClass('SomeClass');
$constants = $reflectionClass->getConstants(ReflectionClassConstant::IS_PUBLIC));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$reflectionClass = new ReflectionClass('SomeClass');
$reflectionClassConstants = $reflectionClass->getReflectionConstants();
$result = [];
array_walk($reflectionClassConstants, function ($value) use (&$result) {
    if ($value->isPublic()) {
       $result[$value->getName()] = $value->getValue();
    }
});
$constants = $result;
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $args = $node->getArgs();
        $value = $args[0]->value;
        if (!\in_array(\get_class($value), [ClassConstFetch::class, BitwiseOr::class], \true)) {
            return null;
        }
        $classConstFetchNames = [];
        if ($value instanceof ClassConstFetch) {
            $classConstFetchName = $this->resolveClassConstFetchName($value);
            if (\is_string($classConstFetchName)) {
                $classConstFetchNames = [$classConstFetchName];
            }
        }
        if ($value instanceof BitwiseOr) {
            $classConstFetchNames = $this->resolveClassConstFetchNames($value);
        }
        if ($classConstFetchNames !== []) {
            return $this->processClassConstFetches($node, $classConstFetchNames);
        }
        return null;
    }
    /**
     * @param string[] $classConstFetchNames
     */
    private function processClassConstFetches(MethodCall $methodCall, array $classConstFetchNames) : Variable
    {
        $scope = $methodCall->getAttribute(AttributeKey::SCOPE);
        $reflectionClassConstants = $this->variableNaming->createCountedValueName('reflectionClassConstants', $scope);
        $variableReflectionClassConstants = new Variable($this->variableNaming->createCountedValueName($reflectionClassConstants, $scope));
        $assign = new Assign($variableReflectionClassConstants, new MethodCall($methodCall->var, 'getReflectionConstants'));
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression($assign), $methodCall);
        $result = $this->variableNaming->createCountedValueName('result', $scope);
        $variableResult = new Variable($result);
        $assignVariableResult = new Assign($variableResult, new Array_());
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression($assignVariableResult), $methodCall);
        $ifs = [];
        $valueVariable = new Variable('value');
        $key = new MethodCall($valueVariable, 'getName');
        $value = new MethodCall($valueVariable, 'getValue');
        $arrayDimFetch = new ArrayDimFetch($variableResult, $key);
        $assignValue = $value;
        foreach ($classConstFetchNames as $classConstFetchName) {
            $methodCallName = self::MAP_CONSTANT_TO_METHOD[$classConstFetchName];
            $ifs[] = $this->ifManipulator->createIfStmt(new MethodCall($valueVariable, $methodCallName), new Expression(new Assign($arrayDimFetch, $assignValue)));
        }
        $closure = new Closure();
        $closure->params = [new Param(new Variable('value'))];
        $closure->uses = [new ClosureUse($variableResult, \true)];
        $closure->stmts = $ifs;
        $funcCall = $this->nodeFactory->createFuncCall('array_walk', [$variableReflectionClassConstants, $closure]);
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression($funcCall), $methodCall);
        return $variableResult;
    }
    private function resolveClassConstFetchName(ClassConstFetch $classConstFetch) : ?string
    {
        if ($this->shouldSkipClassConstFetch($classConstFetch)) {
            return null;
        }
        /** @var Identifier $name */
        $name = $classConstFetch->name;
        return $name->toString();
    }
    /**
     * @return string[]
     */
    private function resolveClassConstFetchNames(BitwiseOr $bitwiseOr) : array
    {
        $values = $this->binaryOpConditionsCollector->findConditions($bitwiseOr, BitwiseOr::class);
        if ($this->shouldSkipBitwiseOrValues($values)) {
            return [];
        }
        $classConstFetchNames = [];
        /** @var ClassConstFetch[] $values */
        foreach ($values as $value) {
            /** @var Identifier $name */
            $name = $value->name;
            $classConstFetchNames[] = $name->toString();
        }
        return \array_unique($classConstFetchNames);
    }
    /**
     * @param Node[] $values
     */
    private function shouldSkipBitwiseOrValues(array $values) : bool
    {
        foreach ($values as $value) {
            if (!$value instanceof ClassConstFetch) {
                return \true;
            }
            if ($this->shouldSkipClassConstFetch($value)) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipClassConstFetch(ClassConstFetch $classConstFetch) : bool
    {
        if (!$classConstFetch->class instanceof FullyQualified) {
            return \true;
        }
        if ($classConstFetch->class->toString() !== 'ReflectionClassConstant') {
            return \true;
        }
        if (!$classConstFetch->name instanceof Identifier) {
            return \true;
        }
        $constants = \array_keys(self::MAP_CONSTANT_TO_METHOD);
        return !$this->nodeNameResolver->isNames($classConstFetch->name, $constants);
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if (!$this->nodeNameResolver->isName($methodCall->name, 'getConstants')) {
            return \true;
        }
        $varType = $this->nodeTypeResolver->getType($methodCall->var);
        if (!$varType instanceof ObjectType) {
            return \true;
        }
        if ($varType->getClassName() !== 'ReflectionClass') {
            return \true;
        }
        return $methodCall->getArgs() === [];
    }
}
