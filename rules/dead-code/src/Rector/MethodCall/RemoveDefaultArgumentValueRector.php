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
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionFunction;

/**
 * @see \Rector\DeadCode\Tests\Rector\MethodCall\RemoveDefaultArgumentValueRector\RemoveDefaultArgumentValueRectorTest
 */
final class RemoveDefaultArgumentValueRector extends AbstractRector
{
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
        if ($keysToRemove === []) {
            return null;
        }

        foreach ($keysToRemove as $keyToRemove) {
            $this->removeArg($node, $keyToRemove);
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

        if (! $node->name instanceof Name) {
            return true;
        }

        $functionName = $this->getName($node);
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
     * @return \PhpParser\Node[]
     */
    private function resolveDefaultValuesFromCall(Node $node): array
    {
        $nodeName = $this->resolveNodeName($node);
        if ($nodeName === null) {
            return [];
        }

        if ($node instanceof FuncCall) {
            return $this->resolveFuncCallDefaultParamValues($nodeName);
        }

        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        // anonymous class
        if ($className === null) {
            return [];
        }

        $classMethodNode = $this->functionLikeParsedNodesFinder->findClassMethod($nodeName, $className);
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

        /** @var int[] $keysToRemove */
        return $keysToRemove;
    }

    /**
     * @param StaticCall|FuncCall|MethodCall $node
     */
    private function resolveNodeName(Node $node): ?string
    {
        if ($node instanceof FuncCall) {
            return $this->getName($node);
        }

        return $this->getName($node->name);
    }

    /**
     * @return Node[]|Expr[]
     */
    private function resolveFuncCallDefaultParamValues(string $nodeName): array
    {
        $functionNode = $this->functionLikeParsedNodesFinder->findFunction($nodeName);
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
