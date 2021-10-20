<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\ClassMethod;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
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
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\ArgumentAdderRectorTest
 */
final class ArgumentAdderRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ADDED_ARGUMENTS = 'added_arguments';
    /**
     * @var ArgumentAdder[]
     */
    private $addedArguments = [];
    /**
     * @var bool
     */
    private $haveArgumentsChanged = \false;
    /**
     * @var \Rector\Arguments\NodeAnalyzer\ArgumentAddingScope
     */
    private $argumentAddingScope;
    /**
     * @var \Rector\Arguments\NodeAnalyzer\ChangedArgumentsDetector
     */
    private $changedArgumentsDetector;
    public function __construct(\Rector\Arguments\NodeAnalyzer\ArgumentAddingScope $argumentAddingScope, \Rector\Arguments\NodeAnalyzer\ChangedArgumentsDetector $changedArgumentsDetector)
    {
        $this->argumentAddingScope = $argumentAddingScope;
        $this->changedArgumentsDetector = $changedArgumentsDetector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        $objectType = new \PHPStan\Type\ObjectType('SomeType');
        $exampleConfiguration = [self::ADDED_ARGUMENTS => [new \Rector\Arguments\ValueObject\ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', \true, $objectType)]];
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('This Rector adds new default arguments in calls of defined methods and class types.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, $exampleConfiguration)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        $this->haveArgumentsChanged = \false;
        foreach ($this->addedArguments as $addedArgument) {
            if (!$this->isObjectTypeMatch($node, $addedArgument->getObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $addedArgument->getMethod())) {
                continue;
            }
            $this->processPositionWithDefaultValues($node, $addedArgument);
        }
        if ($this->haveArgumentsChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, ArgumentAdder[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $addedArguments = $configuration[self::ADDED_ARGUMENTS] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($addedArguments, \Rector\Arguments\ValueObject\ArgumentAdder::class);
        $this->addedArguments = $addedArguments;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod $node
     */
    private function isObjectTypeMatch($node, \PHPStan\Type\ObjectType $objectType) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return $this->isObjectType($node->var, $objectType);
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->isObjectType($node->class, $objectType);
        }
        // ClassMethod
        /** @var Class_|null $classLike */
        $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        // anonymous class
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        return $this->isObjectType($classLike, $objectType);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processPositionWithDefaultValues($node, \Rector\Arguments\ValueObject\ArgumentAdder $argumentAdder) : void
    {
        if ($this->shouldSkipParameter($node, $argumentAdder)) {
            return;
        }
        $defaultValue = $argumentAdder->getArgumentDefaultValue();
        $argumentType = $argumentAdder->getArgumentType();
        $position = $argumentAdder->getPosition();
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $this->addClassMethodParam($node, $argumentAdder, $defaultValue, $argumentType, $position);
        } elseif ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            $this->processStaticCall($node, $position, $argumentAdder);
        } else {
            $arg = new \PhpParser\Node\Arg(\PhpParser\BuilderHelpers::normalizeValue($defaultValue));
            if (isset($node->args[$position])) {
                return;
            }
            $node->args[$position] = $arg;
            $this->haveArgumentsChanged = \true;
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function shouldSkipParameter($node, \Rector\Arguments\ValueObject\ArgumentAdder $argumentAdder) : bool
    {
        $position = $argumentAdder->getPosition();
        $argumentName = $argumentAdder->getArgumentName();
        if ($argumentName === null) {
            return \true;
        }
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
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
        // is correct scope?
        return !$this->argumentAddingScope->isInCorrectScope($node, $argumentAdder);
    }
    /**
     * @param mixed $defaultValue
     */
    private function addClassMethodParam(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\Arguments\ValueObject\ArgumentAdder $argumentAdder, $defaultValue, ?\PHPStan\Type\Type $type, int $position) : void
    {
        $argumentName = $argumentAdder->getArgumentName();
        if ($argumentName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $param = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($argumentName), \PhpParser\BuilderHelpers::normalizeValue($defaultValue));
        if ($type !== null) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::PARAM());
            $param->type = $typeNode;
        }
        $classMethod->params[$position] = $param;
        $this->haveArgumentsChanged = \true;
    }
    private function processStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall, int $position, \Rector\Arguments\ValueObject\ArgumentAdder $argumentAdder) : void
    {
        $argumentName = $argumentAdder->getArgumentName();
        if ($argumentName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if (!$staticCall->class instanceof \PhpParser\Node\Name) {
            return;
        }
        if (!$this->isName($staticCall->class, 'parent')) {
            return;
        }
        $staticCall->args[$position] = new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\Variable($argumentName));
        $this->haveArgumentsChanged = \true;
    }
}
