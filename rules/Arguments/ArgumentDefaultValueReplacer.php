<?php

declare(strict_types=1);

namespace Rector\Arguments;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;

final class ArgumentDefaultValueReplacer
{
    public function __construct(
        private NodeFactory $nodeFactory,
        private ValueResolver $valueResolver
    ) {
    }

    public function processReplaces(
        MethodCall | StaticCall | ClassMethod | FuncCall $node,
        ReplaceArgumentDefaultValueInterface $replaceArgumentDefaultValue
    ): ?Node {
        if ($node instanceof ClassMethod) {
            if (! isset($node->params[$replaceArgumentDefaultValue->getPosition()])) {
                return null;
            }
        } elseif (isset($node->args[$replaceArgumentDefaultValue->getPosition()])) {
            $this->processArgs($node, $replaceArgumentDefaultValue);
        }

        return $node;
    }

    private function processArgs(
        MethodCall | StaticCall | FuncCall $expr,
        ReplaceArgumentDefaultValueInterface $replaceArgumentDefaultValue
    ): void {
        $position = $replaceArgumentDefaultValue->getPosition();

        $argValue = $this->valueResolver->getValue($expr->args[$position]->value);

        if (is_scalar(
            $replaceArgumentDefaultValue->getValueBefore()
        ) && $argValue === $replaceArgumentDefaultValue->getValueBefore()) {
            $expr->args[$position] = $this->normalizeValueToArgument($replaceArgumentDefaultValue->getValueAfter());
        } elseif (is_array($replaceArgumentDefaultValue->getValueBefore())) {
            $newArgs = $this->processArrayReplacement($expr->args, $replaceArgumentDefaultValue);

            if ($newArgs) {
                $expr->args = $newArgs;
            }
        }
    }

    /**
     * @param mixed $value
     */
    private function normalizeValueToArgument($value): Arg
    {
        // class constants → turn string to composite
        if (is_string($value) && \str_contains($value, '::')) {
            [$class, $constant] = explode('::', $value);
            $classConstFetch = $this->nodeFactory->createClassConstFetch($class, $constant);

            return new Arg($classConstFetch);
        }

        return new Arg(BuilderHelpers::normalizeValue($value));
    }

    /**
     * @param Arg[] $argumentNodes
     * @return Arg[]|null
     */
    private function processArrayReplacement(
        array $argumentNodes,
        ReplaceArgumentDefaultValueInterface $replaceArgumentDefaultValue
    ): ?array {
        $argumentValues = $this->resolveArgumentValuesToBeforeRecipe($argumentNodes, $replaceArgumentDefaultValue);
        if ($argumentValues !== $replaceArgumentDefaultValue->getValueBefore()) {
            return null;
        }

        if (is_string($replaceArgumentDefaultValue->getValueAfter())) {
            $argumentNodes[$replaceArgumentDefaultValue->getPosition()] = $this->normalizeValueToArgument(
                $replaceArgumentDefaultValue->getValueAfter()
            );

            // clear following arguments
            $argumentCountToClear = count($replaceArgumentDefaultValue->getValueBefore());
            for ($i = $replaceArgumentDefaultValue->getPosition() + 1; $i <= $replaceArgumentDefaultValue->getPosition() + $argumentCountToClear; ++$i) {
                unset($argumentNodes[$i]);
            }
        }

        return $argumentNodes;
    }

    /**
     * @param Arg[] $argumentNodes
     * @return mixed[]
     */
    private function resolveArgumentValuesToBeforeRecipe(
        array $argumentNodes,
        ReplaceArgumentDefaultValueInterface $replaceArgumentDefaultValue
    ): array {
        $argumentValues = [];

        /** @var mixed[] $valueBefore */
        $valueBefore = $replaceArgumentDefaultValue->getValueBefore();
        $beforeArgumentCount = count($valueBefore);

        for ($i = 0; $i < $beforeArgumentCount; ++$i) {
            if (! isset($argumentNodes[$replaceArgumentDefaultValue->getPosition() + $i])) {
                continue;
            }

            $nextArg = $argumentNodes[$replaceArgumentDefaultValue->getPosition() + $i];
            $argumentValues[] = $this->valueResolver->getValue($nextArg->value);
        }

        return $argumentValues;
    }
}
