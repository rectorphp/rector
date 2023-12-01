<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\ClassMethod;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Arguments\NodeAnalyzer\ArgumentAddingScope;
use Rector\Arguments\NodeAnalyzer\ChangedArgumentsDetector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202312\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\ArgumentAdderRectorTest
 */
final class ArgumentAdderRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Arguments\NodeAnalyzer\ArgumentAddingScope
     */
    private $argumentAddingScope;
    /**
     * @readonly
     * @var \Rector\Arguments\NodeAnalyzer\ChangedArgumentsDetector
     */
    private $changedArgumentsDetector;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var ArgumentAdder[]
     */
    private $addedArguments = [];
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(ArgumentAddingScope $argumentAddingScope, ChangedArgumentsDetector $changedArgumentsDetector, AstResolver $astResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->argumentAddingScope = $argumentAddingScope;
        $this->changedArgumentsDetector = $changedArgumentsDetector;
        $this->astResolver = $astResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('This Rector adds new default arguments in calls of defined methods and class types.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->someMethod();

class MyCustomClass extends SomeExampleClass
{
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->someMethod(true);

class MyCustomClass extends SomeExampleClass
{
    public function someMethod($value = true)
    {
    }
}
CODE_SAMPLE
, [new ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', \true, new ObjectType('SomeType'))])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class, Class_::class];
    }
    /**
     * @param MethodCall|StaticCall|Class_ $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\Class_|null
     */
    public function refactor(Node $node)
    {
        $this->hasChanged = \false;
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $this->refactorCall($node);
        } else {
            foreach ($node->getMethods() as $classMethod) {
                $this->refactorClassMethod($node, $classMethod);
            }
        }
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, ArgumentAdder::class);
        $this->addedArguments = $configuration;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function isObjectTypeMatch($call, ObjectType $objectType) : bool
    {
        if ($call instanceof MethodCall) {
            return $this->isObjectType($call->var, $objectType);
        }
        return $this->isObjectType($call->class, $objectType);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processPositionWithDefaultValues($node, ArgumentAdder $argumentAdder) : void
    {
        if ($this->shouldSkipParameter($node, $argumentAdder)) {
            return;
        }
        $defaultValue = $argumentAdder->getArgumentDefaultValue();
        $argumentType = $argumentAdder->getArgumentType();
        $position = $argumentAdder->getPosition();
        if ($node instanceof ClassMethod) {
            $this->addClassMethodParam($node, $argumentAdder, $defaultValue, $argumentType, $position);
            return;
        }
        if ($node instanceof StaticCall) {
            $this->processStaticCall($node, $position, $argumentAdder);
            return;
        }
        $this->processMethodCall($node, $defaultValue, $position);
    }
    /**
     * @param mixed $defaultValue
     */
    private function processMethodCall(MethodCall $methodCall, $defaultValue, int $position) : void
    {
        $arg = new Arg(BuilderHelpers::normalizeValue($defaultValue));
        if (isset($methodCall->args[$position])) {
            return;
        }
        $this->fillGapBetweenWithDefaultValue($methodCall, $position);
        $methodCall->args[$position] = $arg;
        $this->hasChanged = \true;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function fillGapBetweenWithDefaultValue($node, int $position) : void
    {
        $lastPosition = \count($node->getArgs()) - 1;
        if ($position <= $lastPosition) {
            return;
        }
        if ($position - $lastPosition === 1) {
            return;
        }
        $classMethod = $this->astResolver->resolveClassMethodFromCall($node);
        if (!$classMethod instanceof ClassMethod) {
            return;
        }
        for ($index = $lastPosition + 1; $index < $position; ++$index) {
            $param = $classMethod->params[$index];
            if (!$param->default instanceof Expr) {
                throw new ShouldNotHappenException('Previous position does not have default value');
            }
            $node->args[$index] = new Arg($this->nodeFactory->createReprintedExpr($param->default));
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function shouldSkipParameter($node, ArgumentAdder $argumentAdder) : bool
    {
        $position = $argumentAdder->getPosition();
        $argumentName = $argumentAdder->getArgumentName();
        if ($argumentName === null) {
            return \true;
        }
        if ($node instanceof ClassMethod) {
            // already added?
            if (!isset($node->params[$position])) {
                return \false;
            }
            $param = $node->params[$position];
            // argument added and name has been changed
            if (!$this->isName($param, $argumentName)) {
                return \true;
            }
            // argument added and default has been changed
            if ($this->changedArgumentsDetector->isDefaultValueChanged($param, $argumentAdder->getArgumentDefaultValue())) {
                return \true;
            }
            // argument added and type has been changed
            return $this->changedArgumentsDetector->isTypeChanged($param, $argumentAdder->getArgumentType());
        }
        if (isset($node->args[$position])) {
            return \true;
        }
        // Check if default value is the same
        $classMethod = $this->astResolver->resolveClassMethodFromCall($node);
        if (!$classMethod instanceof ClassMethod) {
            // is correct scope?
            return !$this->argumentAddingScope->isInCorrectScope($node, $argumentAdder);
        }
        if (!isset($classMethod->params[$position])) {
            // is correct scope?
            return !$this->argumentAddingScope->isInCorrectScope($node, $argumentAdder);
        }
        if ($this->changedArgumentsDetector->isDefaultValueChanged($classMethod->params[$position], $argumentAdder->getArgumentDefaultValue())) {
            // is correct scope?
            return !$this->argumentAddingScope->isInCorrectScope($node, $argumentAdder);
        }
        return \true;
    }
    /**
     * @param mixed $defaultValue
     */
    private function addClassMethodParam(ClassMethod $classMethod, ArgumentAdder $argumentAdder, $defaultValue, ?Type $type, int $position) : void
    {
        $argumentName = $argumentAdder->getArgumentName();
        if ($argumentName === null) {
            throw new ShouldNotHappenException();
        }
        $param = new Param(new Variable($argumentName), BuilderHelpers::normalizeValue($defaultValue));
        if ($type instanceof Type) {
            $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PARAM);
        }
        $classMethod->params[$position] = $param;
        $this->hasChanged = \true;
    }
    private function processStaticCall(StaticCall $staticCall, int $position, ArgumentAdder $argumentAdder) : void
    {
        $argumentName = $argumentAdder->getArgumentName();
        if ($argumentName === null) {
            throw new ShouldNotHappenException();
        }
        if (!$staticCall->class instanceof Name) {
            return;
        }
        if (!$this->isName($staticCall->class, ObjectReference::PARENT)) {
            return;
        }
        $this->fillGapBetweenWithDefaultValue($staticCall, $position);
        $staticCall->args[$position] = new Arg(new Variable($argumentName));
        $this->hasChanged = \true;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    private function refactorCall($call) : void
    {
        $callName = $this->getName($call->name);
        if ($callName === null) {
            return;
        }
        foreach ($this->addedArguments as $addedArgument) {
            if (!$this->nodeNameResolver->isStringName($callName, $addedArgument->getMethod())) {
                continue;
            }
            if (!$this->isObjectTypeMatch($call, $addedArgument->getObjectType())) {
                continue;
            }
            $this->processPositionWithDefaultValues($call, $addedArgument);
        }
    }
    private function refactorClassMethod(Class_ $class, ClassMethod $classMethod) : void
    {
        foreach ($this->addedArguments as $addedArgument) {
            if (!$this->isName($classMethod, $addedArgument->getMethod())) {
                continue;
            }
            if (!$this->isObjectType($class, $addedArgument->getObjectType())) {
                continue;
            }
            $this->processPositionWithDefaultValues($classMethod, $addedArgument);
        }
    }
}
