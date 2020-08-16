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
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\ArgumentAdderRector\ArgumentAdderRectorTest
 */
final class ArgumentAdderRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const POSITION_WITH_DEFAULT_VALUE_BY_METHOD_NAMES_BY_CLASS_TYPES = '$positionWithDefaultValueByMethodNamesByClassTypes';

    /**
     * @var string[][][][][]
     */
    private const CONFIGURATION = [
        self::POSITION_WITH_DEFAULT_VALUE_BY_METHOD_NAMES_BY_CLASS_TYPES => [
            'SomeExampleClass' => [
                'someMethod' => [
                    0 => [
                        'name' => 'someArgument',
                        'default_value' => 'true',
                        'type' => 'SomeType',
                    ],
                ],
            ],
        ],
    ];

    /**
     * @var mixed[]
     */
    private $positionWithDefaultValueByMethodNamesByClassTypes = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'This Rector adds new default arguments in calls of defined methods and class types.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->someMethod();
PHP
                    ,
                    <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->someMethod(true);
PHP
                    ,
                    self::CONFIGURATION
                ),
                new ConfiguredCodeSample(
                    <<<'PHP'
class MyCustomClass extends SomeExampleClass
{
    public function someMethod()
    {
    }
}
PHP
                    ,
                    <<<'PHP'
class MyCustomClass extends SomeExampleClass
{
    public function someMethod($value = true)
    {
    }
}
PHP
                    ,
                    self::CONFIGURATION
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
        foreach ($this->positionWithDefaultValueByMethodNamesByClassTypes as $type => $positionWithDefaultValueByMethodNames) {
            if (! $this->isObjectTypeMatch($node, $type)) {
                continue;
            }

            foreach ($positionWithDefaultValueByMethodNames as $method => $positionWithDefaultValues) {
                if (! $this->isName($node->name, $method)) {
                    continue;
                }

                $this->processPositionWithDefaultValues($node, $positionWithDefaultValues);
            }
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->positionWithDefaultValueByMethodNamesByClassTypes = $configuration[self::POSITION_WITH_DEFAULT_VALUE_BY_METHOD_NAMES_BY_CLASS_TYPES] ?? [];
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
        if ($classLike === null) {
            return false;
        }

        return $this->isObjectType($classLike, $type);
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     * @param mixed[] $positionWithDefaultValues
     */
    private function processPositionWithDefaultValues(Node $node, array $positionWithDefaultValues): void
    {
        foreach ($positionWithDefaultValues as $position => $parameterConfiguration) {
            $name = $parameterConfiguration['name'];
            $defaultValue = $parameterConfiguration['default_value'] ?? null;
            $type = $parameterConfiguration['type'] ?? null;

            if ($this->shouldSkipParameter($node, $position, $name, $parameterConfiguration)) {
                continue;
            }

            if ($node instanceof ClassMethod) {
                $this->addClassMethodParam($node, $name, $defaultValue, $type, $position);
            } elseif ($node instanceof StaticCall) {
                $this->processStaticCall($node, $position, $name);
            } else {
                $arg = new Arg(BuilderHelpers::normalizeValue($defaultValue));
                $node->args[$position] = $arg;
            }
        }
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     * @param mixed[] $parameterConfiguration
     */
    private function shouldSkipParameter(Node $node, int $position, string $name, array $parameterConfiguration): bool
    {
        if ($node instanceof ClassMethod) {
            // already added?
            return isset($node->params[$position]) && $this->isName($node->params[$position], $name);
        }

        // already added?
        if (isset($node->args[$position]) && $this->isName($node->args[$position], $name)) {
            return true;
        }

        // is correct scope?
        return ! $this->isInCorrectScope($node, $parameterConfiguration);
    }

    /**
     * @param mixed $defaultValue
     */
    private function addClassMethodParam(
        ClassMethod $classMethod,
        string $name,
        $defaultValue,
        ?string $type,
        int $position
    ): void {
        $param = new Param(new Variable($name), BuilderHelpers::normalizeValue($defaultValue));
        if ($type) {
            $param->type = ctype_upper($type[0]) ? new FullyQualified($type) : new Identifier($type);
        }

        $classMethod->params[$position] = $param;
    }

    private function processStaticCall(StaticCall $staticCall, int $position, string $name): void
    {
        if (! $staticCall->class instanceof Name) {
            return;
        }

        if (! $this->isName($staticCall->class, 'parent')) {
            return;
        }

        $staticCall->args[$position] = new Arg(new Variable($name));
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     * @param mixed[] $parameterConfiguration
     */
    private function isInCorrectScope(Node $node, array $parameterConfiguration): bool
    {
        if (! isset($parameterConfiguration['scope'])) {
            return true;
        }

        /** @var string[] $scope */
        $scope = $parameterConfiguration['scope'];

        if ($node instanceof ClassMethod) {
            return in_array('class_method', $scope, true);
        }

        if ($node instanceof StaticCall) {
            if (! $node->class instanceof Name) {
                return false;
            }

            if ($this->isName($node->class, 'parent')) {
                return in_array('parent_call', $scope, true);
            }

            return in_array('method_call', $scope, true);
        }

        // MethodCall
        return in_array('method_call', $scope, true);
    }
}
