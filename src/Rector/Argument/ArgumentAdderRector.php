<?php declare(strict_types=1);

namespace Rector\Rector\Argument;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArgumentAdderRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $positionWithDefaultValueByMethodNamesByClassTypes = [];

    /**
     * @param mixed[] $positionWithDefaultValueByMethodNamesByClassTypes
     */
    public function __construct(array $positionWithDefaultValueByMethodNamesByClassTypes)
    {
        $this->positionWithDefaultValueByMethodNamesByClassTypes = $positionWithDefaultValueByMethodNamesByClassTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        $configuration = [
            'SomeExampleClass' => [
                'someMethod' => [
                    0 => [
                        'name' => 'someArgument',
                        'default_value' => 'true',
                        'type' => 'SomeType',
                    ],
                ],
            ],
        ];

        return new RectorDefinition(
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
                    $configuration
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
                    $configuration
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
            if (! $this->isType($node, $type)) {
                continue;
            }

            foreach ($positionWithDefaultValueByMethodNames as $method => $positionWithDefaultValues) {
                if (! $this->isName($node, $method)) {
                    continue;
                }

                $this->processPositionWithDefaultValues($node, $positionWithDefaultValues);
            }
        }

        return $node;
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

            if ($this->shouldSkipParameter($node, $position, $name)) {
                continue;
            }

            if ($node instanceof ClassMethod) {
                $this->addClassMethodParam($node, $name, $defaultValue, $type, $position);
            } else {
                $arg = new Arg(BuilderHelpers::normalizeValue($defaultValue));
                $node->args[$position] = $arg;
            }
        }
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

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     */
    private function shouldSkipParameter(Node $node, int $position, string $name): bool
    {
        if ($node instanceof ClassMethod) {
            // already added?
            return isset($node->params[$position]) && $this->isName($node->params[$position], $name);
        }

        // already added?
        return isset($node->args[$position]) && $this->isName($node->args[$position], $name);
    }
}
