<?php

declare(strict_types=1);

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
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\ArgumentAdderRectorTest
 */
final class ArgumentAdderRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ADDED_ARGUMENTS = 'added_arguments';

    /**
     * @var ArgumentAdder[]
     */
    private array $addedArguments = [];

    private bool $haveArgumentsChanged = false;

    public function __construct(
        private ArgumentAddingScope $argumentAddingScope,
        private ChangedArgumentsDetector $changedArgumentsDetector
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $objectType = new ObjectType('SomeType');

        $exampleConfiguration = [
            self::ADDED_ARGUMENTS => [
                new ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', true, $objectType),
            ],
        ];

        return new RuleDefinition(
            'This Rector adds new default arguments in calls of defined methods and class types.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->someMethod();

class MyCustomClass extends SomeExampleClass
{
    public function someMethod()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->someMethod(true);

class MyCustomClass extends SomeExampleClass
{
    public function someMethod($value = true)
    {
    }
}
CODE_SAMPLE
                    ,
                    $exampleConfiguration
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): MethodCall | StaticCall | ClassMethod | null
    {
        $this->haveArgumentsChanged = false;

        foreach ($this->addedArguments as $addedArgument) {
            if (! $this->isObjectTypeMatch($node, $addedArgument->getObjectType())) {
                continue;
            }

            if (! $this->isName($node->name, $addedArgument->getMethod())) {
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
    public function configure(array $configuration): void
    {
        $addedArguments = $configuration[self::ADDED_ARGUMENTS] ?? ($configuration ?: []);
        Assert::allIsInstanceOf($addedArguments, ArgumentAdder::class);
        $this->addedArguments = $addedArguments;
    }

    private function isObjectTypeMatch(MethodCall | StaticCall | ClassMethod $node, ObjectType $objectType): bool
    {
        if ($node instanceof MethodCall) {
            return $this->isObjectType($node->var, $objectType);
        }

        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $objectType);
        }

        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (! $classLike instanceof Class_) {
            return false;
        }

        return $this->isObjectType($classLike, $objectType);
    }

    private function processPositionWithDefaultValues(
        ClassMethod | MethodCall | StaticCall $node,
        ArgumentAdder $argumentAdder
    ): void {
        if ($this->shouldSkipParameter($node, $argumentAdder)) {
            return;
        }

        $defaultValue = $argumentAdder->getArgumentDefaultValue();
        $argumentType = $argumentAdder->getArgumentType();

        $position = $argumentAdder->getPosition();

        if ($node instanceof ClassMethod) {
            $this->addClassMethodParam($node, $argumentAdder, $defaultValue, $argumentType, $position);
        } elseif ($node instanceof StaticCall) {
            $this->processStaticCall($node, $position, $argumentAdder);
        } else {
            $arg = new Arg(BuilderHelpers::normalizeValue($defaultValue));
            if (isset($node->args[$position])) {
                return;
            }

            $node->args[$position] = $arg;
            $this->haveArgumentsChanged = true;
        }
    }

    private function shouldSkipParameter(
        ClassMethod | MethodCall | StaticCall $node,
        ArgumentAdder $argumentAdder
    ): bool {
        $position = $argumentAdder->getPosition();
        $argumentName = $argumentAdder->getArgumentName();

        if ($argumentName === null) {
            return true;
        }

        if ($node instanceof ClassMethod) {
            // already added?
            if (! isset($node->params[$position])) {
                return false;
            }

            $param = $node->params[$position];
            // argument added and name has been changed
            if (! $this->isName($param, $argumentName)) {
                return true;
            }

            // argument added and default has been changed
            if ($this->changedArgumentsDetector->isDefaultValueChanged(
                $param,
                $argumentAdder->getArgumentDefaultValue()
            )) {
                return true;
            }

            // argument added and type has been changed
            return $this->changedArgumentsDetector->isTypeChanged($param, $argumentAdder->getArgumentType());
        }

        if (isset($node->args[$position])) {
            return true;
        }

        // is correct scope?
        return ! $this->argumentAddingScope->isInCorrectScope($node, $argumentAdder);
    }

    private function addClassMethodParam(
        ClassMethod $classMethod,
        ArgumentAdder $argumentAdder,
        mixed $defaultValue,
        ?Type $type,
        int $position
    ): void {
        $argumentName = $argumentAdder->getArgumentName();
        if ($argumentName === null) {
            throw new ShouldNotHappenException();
        }

        $param = new Param(new Variable($argumentName), BuilderHelpers::normalizeValue($defaultValue));
        if ($type !== null) {
            $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PARAM());
            $param->type = $typeNode;
        }

        $classMethod->params[$position] = $param;
        $this->haveArgumentsChanged = true;
    }

    private function processStaticCall(StaticCall $staticCall, int $position, ArgumentAdder $argumentAdder): void
    {
        $argumentName = $argumentAdder->getArgumentName();
        if ($argumentName === null) {
            throw new ShouldNotHappenException();
        }

        if (! $staticCall->class instanceof Name) {
            return;
        }

        if (! $this->isName($staticCall->class, ObjectReference::PARENT()->getValue())) {
            return;
        }

        $staticCall->args[$position] = new Arg(new Variable($argumentName));
        $this->haveArgumentsChanged = true;
    }
}
