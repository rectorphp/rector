<?php

declare (strict_types=1);
namespace Rector\Arguments\Rector\ClassMethod;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
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
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\ArgumentAdderRectorTest
 */
final class ArgumentAdderRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var ArgumentAdder[]
     */
    private $addedArguments = [];
    /**
     * @var bool
     */
    private $haveArgumentsChanged = \false;
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
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(\Rector\Arguments\NodeAnalyzer\ArgumentAddingScope $argumentAddingScope, \Rector\Arguments\NodeAnalyzer\ChangedArgumentsDetector $changedArgumentsDetector, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter)
    {
        $this->argumentAddingScope = $argumentAddingScope;
        $this->changedArgumentsDetector = $changedArgumentsDetector;
        $this->astResolver = $astResolver;
        $this->nodePrinter = $nodePrinter;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
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
, [new \Rector\Arguments\ValueObject\ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', \true, new \PHPStan\Type\ObjectType('SomeType'))])]);
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
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Arguments\ValueObject\ArgumentAdder::class);
        $this->addedArguments = $configuration;
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
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
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
            return;
        }
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            $this->processStaticCall($node, $position, $argumentAdder);
            return;
        }
        $this->processMethodCall($node, $defaultValue, $position);
    }
    /**
     * @param mixed $defaultValue
     */
    private function processMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall, $defaultValue, int $position) : void
    {
        $arg = new \PhpParser\Node\Arg(\PhpParser\BuilderHelpers::normalizeValue($defaultValue));
        if (isset($methodCall->args[$position])) {
            return;
        }
        $this->fillGapBetweenWithDefaultValue($methodCall, $position);
        $methodCall->args[$position] = $arg;
        $this->haveArgumentsChanged = \true;
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
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        for ($index = $lastPosition + 1; $index < $position; ++$index) {
            $param = $classMethod->params[$index];
            if (!$param->default instanceof \PhpParser\Node\Expr) {
                throw new \Rector\Core\Exception\ShouldNotHappenException('Previous position does not has default value');
            }
            $default = $this->nodePrinter->print($param->default);
            $node->args[$index] = new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name($default)));
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
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM());
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
        if (!$this->isName($staticCall->class, \Rector\Core\Enum\ObjectReference::PARENT()->getValue())) {
            return;
        }
        $this->fillGapBetweenWithDefaultValue($staticCall, $position);
        $staticCall->args[$position] = new \PhpParser\Node\Arg(new \PhpParser\Node\Expr\Variable($argumentName));
        $this->haveArgumentsChanged = \true;
    }
}
