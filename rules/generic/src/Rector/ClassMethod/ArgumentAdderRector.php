<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Generic\NodeAnalyzer\ArgumentAddingScope;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\ArgumentAdderRectorTest
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
    private $addedArguments = [];

    /**
     * @var ArgumentAddingScope
     */
    private $argumentAddingScope;

    public function __construct(ArgumentAddingScope $argumentAddingScope)
    {
        $this->argumentAddingScope = $argumentAddingScope;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $exampleConfiguration = [
            self::ADDED_ARGUMENTS => [
                new ArgumentAdder('SomeExampleClass', 'someMethod', 0, 'someArgument', 'true', 'SomeType'),
            ],
        ];

        return new RuleDefinition(
            'This Rector adds new default arguments in calls of defined methods and class types.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->someMethod();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->someMethod(true);
CODE_SAMPLE
                    ,
                    $exampleConfiguration
                ),
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
class MyCustomClass extends SomeExampleClass
{
    public function someMethod()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
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
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->addedArguments as $addedArgument) {
            if (! $this->isObjectTypeMatch($node, $addedArgument->getClass())) {
                continue;
            }

            if (! $this->isName($node->name, $addedArgument->getMethod())) {
                continue;
            }

            $this->processPositionWithDefaultValues($node, $addedArgument);
        }

        return $node;
    }

    /**
     * @param array<string, ArgumentAdder[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $addedArguments = $configuration[self::ADDED_ARGUMENTS] ?? [];
        Assert::allIsInstanceOf($addedArguments, ArgumentAdder::class);
        $this->addedArguments = $addedArguments;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function isObjectTypeMatch(Node $node, string $type): bool
    {
        if ($node instanceof MethodCall) {
            return $this->isObjectType($node->var, $type);
        }

        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $type);
        }

        // ClassMethod
        /** @var Class_|null $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);

        // anonymous class
        if (! $classLike instanceof Class_) {
            return false;
        }

        return $this->isObjectType($classLike, $type);
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     */
    private function processPositionWithDefaultValues(Node $node, ArgumentAdder $argumentAdder): void
    {
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
        }
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     */
    private function shouldSkipParameter(Node $node, ArgumentAdder $argumentAdder): bool
    {
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

            return $this->isName($node->params[$position], $argumentName);
        }

        // already added?
        if (isset($node->args[$position]) && $this->isName($node->args[$position], $argumentName)) {
            return true;
        }

        // is correct scope?
        return ! $this->argumentAddingScope->isInCorrectScope($node, $argumentAdder);
    }

    /**
     * @param mixed $defaultValue
     */
    private function addClassMethodParam(
        ClassMethod $classMethod,
        ArgumentAdder $argumentAdder,
        $defaultValue,
        ?string $type,
        int $position
    ): void {
        $argumentName = $argumentAdder->getArgumentName();
        if ($argumentName === null) {
            throw new ShouldNotHappenException();
        }

        $param = new Param(new Variable($argumentName), BuilderHelpers::normalizeValue($defaultValue));
        if ($type) {
            $param->type = ctype_upper($type[0]) ? new FullyQualified($type) : new Identifier($type);
        }

        $classMethod->params[$position] = $param;
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

        if (! $this->isName($staticCall->class, 'parent')) {
            return;
        }

        $staticCall->args[$position] = new Arg(new Variable($argumentName));
    }
}
