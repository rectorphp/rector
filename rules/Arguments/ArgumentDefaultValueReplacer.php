<?php

declare(strict_types=1);

namespace Rector\Arguments;

use Nette\Utils\Strings;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Arguments\Contract\ArgumentDefaultValueReplacerInterface;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;

final class ArgumentDefaultValueReplacer
{
    public function __construct(
        private NodeFactory $nodeFactory,
        private ValueResolver $valueResolver
    ) {
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod|Expr\FuncCall $node
     */
    public function processReplaces(
        Node $node,
        ArgumentDefaultValueReplacerInterface $argumentDefaultValueReplacer
    ): ?Node {
        if ($node instanceof ClassMethod) {
            if (! isset($node->params[$argumentDefaultValueReplacer->getPosition()])) {
                return null;
            }
        } elseif (isset($node->args[$argumentDefaultValueReplacer->getPosition()])) {
            $this->processArgs($node, $argumentDefaultValueReplacer);
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall|FuncCall $expr
     */
    private function processArgs(Expr $expr, ArgumentDefaultValueReplacerInterface $argumentDefaultValueReplacer): void
    {
        $position = $argumentDefaultValueReplacer->getPosition();

        $argValue = $this->valueResolver->getValue($expr->args[$position]->value);

        if (is_scalar(
            $argumentDefaultValueReplacer->getValueBefore()
        ) && $argValue === $argumentDefaultValueReplacer->getValueBefore()) {
            $expr->args[$position] = $this->normalizeValueToArgument($argumentDefaultValueReplacer->getValueAfter());
        } elseif (is_array($argumentDefaultValueReplacer->getValueBefore())) {
            $newArgs = $this->processArrayReplacement($expr->args, $argumentDefaultValueReplacer);

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
        if (is_string($value) && Strings::contains($value, '::')) {
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
        ArgumentDefaultValueReplacerInterface $argumentDefaultValueReplacer
    ): ?array {
        $argumentValues = $this->resolveArgumentValuesToBeforeRecipe($argumentNodes, $argumentDefaultValueReplacer);
        if ($argumentValues !== $argumentDefaultValueReplacer->getValueBefore()) {
            return null;
        }

        if (is_string($argumentDefaultValueReplacer->getValueAfter())) {
            $argumentNodes[$argumentDefaultValueReplacer->getPosition()] = $this->normalizeValueToArgument(
                $argumentDefaultValueReplacer->getValueAfter()
            );

            // clear following arguments
            $argumentCountToClear = count($argumentDefaultValueReplacer->getValueBefore());
            for ($i = $argumentDefaultValueReplacer->getPosition() + 1; $i <= $argumentDefaultValueReplacer->getPosition() + $argumentCountToClear; ++$i) {
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
        ArgumentDefaultValueReplacerInterface $argumentDefaultValueReplacer
    ): array {
        $argumentValues = [];

        /** @var mixed[] $valueBefore */
        $valueBefore = $argumentDefaultValueReplacer->getValueBefore();
        $beforeArgumentCount = count($valueBefore);

        for ($i = 0; $i < $beforeArgumentCount; ++$i) {
            if (! isset($argumentNodes[$argumentDefaultValueReplacer->getPosition() + $i])) {
                continue;
            }

            $nextArg = $argumentNodes[$argumentDefaultValueReplacer->getPosition() + $i];
            $argumentValues[] = $this->valueResolver->getValue($nextArg->value);
        }

        return $argumentValues;
    }
}
