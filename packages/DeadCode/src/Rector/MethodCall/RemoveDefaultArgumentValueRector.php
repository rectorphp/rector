<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\MethodCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use ReflectionFunction;

/**
 * @see \Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector\RemoveDefaultArgumentValueRectorTest
 */
final class RemoveDefaultArgumentValueRector extends AbstractRector
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(ParsedNodesByType $parsedNodesByType)
    {
        $this->parsedNodesByType = $parsedNodesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove argument value, if it is the same as default value', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $this->runWithDefault([]);
        $card = self::runWithStaticDefault([]);
    }

    public function runWithDefault($items = [])
    {
        return $items;
    }

    public function runStaticWithDefault($cards = [])
    {
        return $cards;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $this->runWithDefault();
        $card = self::runWithStaticDefault();
    }

    public function runWithDefault($items = [])
    {
        return $items;
    }

    public function runStaticWithDefault($cards = [])
    {
        return $cards;
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, FuncCall::class];
    }

    /**
     * @param MethodCall|StaticCall|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $defaultValues = $this->resolveDefaultValuesFromCall($node);

        $keysToRemove = $this->resolveKeysToRemove($node, $defaultValues);
        foreach ($keysToRemove as $keyToRemove) {
            unset($node->args[$keyToRemove]);
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall|FuncCall $node
     */
    private function shouldSkip(Node $node): bool
    {
        if ($node->args === []) {
            return true;
        }

        if (! $node instanceof FuncCall) {
            return false;
        }

        $functionName = $this->getName($node->name);
        if ($functionName === null) {
            return false;
        }

        if (! function_exists($functionName)) {
            return false;
        }

        $reflectionFunction = new ReflectionFunction($functionName);

        // skip native functions, hard to analyze without stubs (stubs would make working with IDE non-practical)
        return $reflectionFunction->isInternal();
    }

    /**
     * @param StaticCall|FuncCall|MethodCall $node
     * @return Expr[]
     */
    private function resolveDefaultValuesFromCall(Node $node): array
    {
        /** @var string|null $nodeName */
        $nodeName = $this->getName($node);
        if ($nodeName === null) {
            return [];
        }

        if ($node instanceof FuncCall) {
            return $this->resolveFuncCallDefaultParamValues($nodeName);
        }

        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) { // anonymous class
            return [];
        }

        $classMethodNode = $this->parsedNodesByType->findMethod($nodeName, $className);
        if ($classMethodNode !== null) {
            return $this->resolveDefaultParamValuesFromFunctionLike($classMethodNode);
        }

        return [];
    }

    /**
     * @param StaticCall|MethodCall|FuncCall $node
     * @param Expr[]|mixed[] $defaultValues
     * @return int[]
     */
    private function resolveKeysToRemove(Node $node, array $defaultValues): array
    {
        $keysToRemove = [];
        $keysToKeep = [];

        /** @var int $key */
        foreach ($node->args as $key => $arg) {
            if (! isset($defaultValues[$key])) {
                $keysToKeep[] = $key;
                continue;
            }

            if ($this->areNodesEqual($defaultValues[$key], $arg->value)) {
                $keysToRemove[] = $key;
            } else {
                $keysToKeep[] = $key;
            }
        }

        if ($keysToRemove === []) {
            return [];
        }

        if ($keysToKeep !== [] && max($keysToKeep) > max($keysToRemove)) {
            return [];
        }

        return $keysToRemove;
    }

    /**
     * @return Expr[]
     */
    private function resolveFuncCallDefaultParamValues(string $nodeName): array
    {
        $functionNode = $this->parsedNodesByType->findFunction($nodeName);
        if ($functionNode !== null) {
            return $this->resolveDefaultParamValuesFromFunctionLike($functionNode);
        }

        // non existing function
        if (! function_exists($nodeName)) {
            return [];
        }

        $reflectionFunction = new ReflectionFunction($nodeName);
        if ($reflectionFunction->isUserDefined()) {
            $defaultValues = [];
            foreach ($reflectionFunction->getParameters() as $key => $reflectionParameter) {
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $defaultValues[$key] = BuilderHelpers::normalizeValue($reflectionParameter->getDefaultValue());
                }
            }

            return $defaultValues;
        }

        return [];
    }

    /**
     * @return Node[]
     */
    private function resolveDefaultParamValuesFromFunctionLike(FunctionLike $functionLike): array
    {
        $defaultValues = [];
        foreach ($functionLike->getParams() as $key => $param) {
            if ($param->default === null) {
                continue;
            }

            $defaultValues[$key] = $param->default;
        }

        return $defaultValues;
    }
}
