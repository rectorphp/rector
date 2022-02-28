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
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeCollector\BinaryOpConditionsCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionClassGetConstantsFilterRector\DowngradeReflectionClassGetConstantsFilterRectorTest
 */
final class DowngradeReflectionClassGetConstantsFilterRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming, \Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\NodeCollector\BinaryOpConditionsCollector $binaryOpConditionsCollector)
    {
        $this->variableNaming = $variableNaming;
        $this->ifManipulator = $ifManipulator;
        $this->binaryOpConditionsCollector = $binaryOpConditionsCollector;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade ReflectionClass->getConstants(ReflectionClassConstant::IS_*)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $args = $node->getArgs();
        $value = $args[0]->value;
        if (!\in_array(\get_class($value), [\PhpParser\Node\Expr\ClassConstFetch::class, \PhpParser\Node\Expr\BinaryOp\BitwiseOr::class], \true)) {
            return null;
        }
        $classConstFetchNames = [];
        if ($value instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            $classConstFetchName = $this->resolveClassConstFetchName($value);
            if (\is_string($classConstFetchName)) {
                $classConstFetchNames = [$classConstFetchName];
            }
        }
        if ($value instanceof \PhpParser\Node\Expr\BinaryOp\BitwiseOr) {
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
    private function processClassConstFetches(\PhpParser\Node\Expr\MethodCall $methodCall, array $classConstFetchNames) : ?\PhpParser\Node\Expr\Variable
    {
        $scope = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $reflectionClassConstants = $this->variableNaming->createCountedValueName('reflectionClassConstants', $scope);
        $currentStmt = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$currentStmt instanceof \PhpParser\Node\Stmt) {
            return null;
        }
        $variableReflectionClassConstants = new \PhpParser\Node\Expr\Variable($this->variableNaming->createCountedValueName($reflectionClassConstants, $scope));
        $assign = new \PhpParser\Node\Expr\Assign($variableReflectionClassConstants, new \PhpParser\Node\Expr\MethodCall($methodCall->var, 'getReflectionConstants'));
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression($assign), $currentStmt);
        $result = $this->variableNaming->createCountedValueName('result', $scope);
        $variableResult = new \PhpParser\Node\Expr\Variable($result);
        $assignVariableResult = new \PhpParser\Node\Expr\Assign($variableResult, new \PhpParser\Node\Expr\Array_());
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression($assignVariableResult), $currentStmt);
        $ifs = [];
        $valueVariable = new \PhpParser\Node\Expr\Variable('value');
        $key = new \PhpParser\Node\Expr\MethodCall($valueVariable, 'getName');
        $value = new \PhpParser\Node\Expr\MethodCall($valueVariable, 'getValue');
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($variableResult, $key);
        $assignValue = $value;
        foreach ($classConstFetchNames as $classConstFetchName) {
            $methodCallName = self::MAP_CONSTANT_TO_METHOD[$classConstFetchName];
            $ifs[] = $this->ifManipulator->createIfExpr(new \PhpParser\Node\Expr\MethodCall($valueVariable, $methodCallName), new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($arrayDimFetch, $assignValue)));
        }
        $closure = new \PhpParser\Node\Expr\Closure();
        $closure->params = [new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable('value'))];
        $closure->uses = [new \PhpParser\Node\Expr\ClosureUse($variableResult, \true)];
        $closure->stmts = $ifs;
        $funcCall = $this->nodeFactory->createFuncCall('array_walk', [$variableReflectionClassConstants, $closure]);
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression($funcCall), $currentStmt);
        return $variableResult;
    }
    private function resolveClassConstFetchName(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch) : ?string
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
    private function resolveClassConstFetchNames(\PhpParser\Node\Expr\BinaryOp\BitwiseOr $bitwiseOr) : array
    {
        $values = $this->binaryOpConditionsCollector->findConditions($bitwiseOr, \PhpParser\Node\Expr\BinaryOp\BitwiseOr::class);
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
            if (!$value instanceof \PhpParser\Node\Expr\ClassConstFetch) {
                return \true;
            }
            if ($this->shouldSkipClassConstFetch($value)) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipClassConstFetch(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch) : bool
    {
        if (!$classConstFetch->class instanceof \PhpParser\Node\Name\FullyQualified) {
            return \true;
        }
        if ($classConstFetch->class->toString() !== 'ReflectionClassConstant') {
            return \true;
        }
        if (!$classConstFetch->name instanceof \PhpParser\Node\Identifier) {
            return \true;
        }
        $constants = \array_keys(self::MAP_CONSTANT_TO_METHOD);
        return !$this->nodeNameResolver->isNames($classConstFetch->name, $constants);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->nodeNameResolver->isName($methodCall->name, 'getConstants')) {
            return \true;
        }
        $varType = $this->nodeTypeResolver->getType($methodCall->var);
        if (!$varType instanceof \PHPStan\Type\ObjectType) {
            return \true;
        }
        if ($varType->getClassName() !== 'ReflectionClass') {
            return \true;
        }
        return $methodCall->getArgs() === [];
    }
}
