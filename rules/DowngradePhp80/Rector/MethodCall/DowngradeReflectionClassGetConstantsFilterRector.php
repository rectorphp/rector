<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\Rector\MethodCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\ClosureUse;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\NodeManipulator\IfManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Naming\Naming\VariableNaming;
use RectorPrefix20220606\Rector\NodeCollector\BinaryOpConditionsCollector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    public function __construct(VariableNaming $variableNaming, IfManipulator $ifManipulator, BinaryOpConditionsCollector $binaryOpConditionsCollector)
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
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression($assign), $methodCall, $this->file->getSmartFileInfo());
        $result = $this->variableNaming->createCountedValueName('result', $scope);
        $variableResult = new Variable($result);
        $assignVariableResult = new Assign($variableResult, new Array_());
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression($assignVariableResult), $methodCall, $this->file->getSmartFileInfo());
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
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression($funcCall), $methodCall, $this->file->getSmartFileInfo());
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
