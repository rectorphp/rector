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
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ObjectType;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeCollector\BinaryOpConditionsCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\DowngradeReflectionClassGetConstantsFilterRectorTest
 */
final class DowngradeReflectionClassGetConstantsFilterRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\NodeCollector\BinaryOpConditionsCollector
     */
    private $binaryOpConditionsCollector;
    /**
     * @var array<string, string>
     */
    private const MAP_CONSTANT_TO_METHOD = ['IS_PUBLIC' => 'isPublic', 'IS_PROTECTED' => 'isProtected', 'IS_PRIVATE' => 'isPrivate'];
    public function __construct(VariableNaming $variableNaming, BinaryOpConditionsCollector $binaryOpConditionsCollector)
    {
        $this->variableNaming = $variableNaming;
        $this->binaryOpConditionsCollector = $binaryOpConditionsCollector;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class];
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
     * @param Expression $node
     * @return Stmt[]
     */
    public function refactor(Node $node) : ?array
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $assign = $node->expr;
        if (!$assign->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $assign->expr;
        if ($this->shouldSkipMethodCall($methodCall)) {
            return null;
        }
        $args = $methodCall->getArgs();
        $value = $args[0]->value;
        if (!$value instanceof ClassConstFetch && !$value instanceof BitwiseOr) {
            return null;
        }
        $classConstFetchNames = $this->resolveClassConstFetchNames($value);
        if ($classConstFetchNames === []) {
            return null;
        }
        return $this->refactorClassConstFetches($methodCall, $classConstFetchNames, $node, $assign);
    }
    /**
     * @param string[] $classConstFetchNames
     * @return Stmt[]
     */
    private function refactorClassConstFetches(MethodCall $methodCall, array $classConstFetchNames, Stmt $stmt, Assign $assign) : array
    {
        $scope = $methodCall->getAttribute(AttributeKey::SCOPE);
        $reflectionClassConstants = $this->variableNaming->createCountedValueName('reflectionClassConstants', $scope);
        $variableReflectionClassConstants = new Variable($this->variableNaming->createCountedValueName($reflectionClassConstants, $scope));
        $getReflectionConstantsAssign = new Assign($variableReflectionClassConstants, new MethodCall($methodCall->var, 'getReflectionConstants'));
        $result = $this->variableNaming->createCountedValueName('result', $scope);
        $resultVariable = new Variable($result);
        $resultVariableAssign = new Assign($resultVariable, new Array_());
        $valueVariable = new Variable('value');
        $key = new MethodCall($valueVariable, 'getName');
        $value = new MethodCall($valueVariable, 'getValue');
        $arrayDimFetch = new ArrayDimFetch($resultVariable, $key);
        $assignValue = $value;
        $ifs = $this->createIfs($classConstFetchNames, $valueVariable, $arrayDimFetch, $assignValue);
        $closure = $this->createClosure($resultVariable, $ifs);
        $arrayWalkFuncCall = $this->nodeFactory->createFuncCall('array_walk', [$variableReflectionClassConstants, $closure]);
        $assign->expr = $resultVariable;
        return [new Expression($getReflectionConstantsAssign), new Expression($resultVariableAssign), new Expression($arrayWalkFuncCall), $stmt];
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
    private function resolveClassConstFetchNamesFromBitwiseOr(BitwiseOr $bitwiseOr) : array
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
    private function shouldSkipMethodCall(MethodCall $methodCall) : bool
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
    /**
     * @param Stmt[] $stmts
     */
    private function createClosure(Variable $variable, array $stmts) : Closure
    {
        $closure = new Closure();
        $closure->params = [new Param(new Variable('value'))];
        $closure->uses = [new ClosureUse($variable, \true)];
        $closure->stmts = $stmts;
        return $closure;
    }
    /**
     * @param string[] $classConstFetchNames
     * @return If_[]
     */
    private function createIfs(array $classConstFetchNames, Variable $valueVariable, ArrayDimFetch $arrayDimFetch, MethodCall $methodCall) : array
    {
        $ifs = [];
        foreach ($classConstFetchNames as $classConstFetchName) {
            $methodCallName = self::MAP_CONSTANT_TO_METHOD[$classConstFetchName];
            $if = new If_(new MethodCall($valueVariable, $methodCallName), ['stmts' => [new Expression(new Assign($arrayDimFetch, $methodCall))]]);
            $ifs[] = $if;
        }
        return $ifs;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\BinaryOp\BitwiseOr $value
     */
    private function resolveClassConstFetchNames($value) : array
    {
        if ($value instanceof ClassConstFetch) {
            $classConstFetchNames = [];
            $classConstFetchName = $this->resolveClassConstFetchName($value);
            if (\is_string($classConstFetchName)) {
                return [$classConstFetchName];
            }
            return $classConstFetchNames;
        }
        return $this->resolveClassConstFetchNamesFromBitwiseOr($value);
    }
}
