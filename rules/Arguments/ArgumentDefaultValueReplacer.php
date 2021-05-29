<?php

declare (strict_types=1);
namespace Rector\Arguments;

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
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @param MethodCall|StaticCall|ClassMethod|Expr\FuncCall $node
     */
    public function processReplaces(\PhpParser\Node $node, \Rector\Arguments\Contract\ArgumentDefaultValueReplacerInterface $argumentDefaultValueReplacer) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            if (!isset($node->params[$argumentDefaultValueReplacer->getPosition()])) {
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
    private function processArgs(\PhpParser\Node\Expr $expr, \Rector\Arguments\Contract\ArgumentDefaultValueReplacerInterface $argumentDefaultValueReplacer) : void
    {
        $position = $argumentDefaultValueReplacer->getPosition();
        $argValue = $this->valueResolver->getValue($expr->args[$position]->value);
        if (\is_scalar($argumentDefaultValueReplacer->getValueBefore()) && $argValue === $argumentDefaultValueReplacer->getValueBefore()) {
            $expr->args[$position] = $this->normalizeValueToArgument($argumentDefaultValueReplacer->getValueAfter());
        } elseif (\is_array($argumentDefaultValueReplacer->getValueBefore())) {
            $newArgs = $this->processArrayReplacement($expr->args, $argumentDefaultValueReplacer);
            if ($newArgs) {
                $expr->args = $newArgs;
            }
        }
    }
    /**
     * @param mixed $value
     */
    private function normalizeValueToArgument($value) : \PhpParser\Node\Arg
    {
        // class constants → turn string to composite
        if (\is_string($value) && \strpos($value, '::') !== \false) {
            [$class, $constant] = \explode('::', $value);
            $classConstFetch = $this->nodeFactory->createClassConstFetch($class, $constant);
            return new \PhpParser\Node\Arg($classConstFetch);
        }
        return new \PhpParser\Node\Arg(\PhpParser\BuilderHelpers::normalizeValue($value));
    }
    /**
     * @param Arg[] $argumentNodes
     * @return Arg[]|null
     */
    private function processArrayReplacement(array $argumentNodes, \Rector\Arguments\Contract\ArgumentDefaultValueReplacerInterface $argumentDefaultValueReplacer) : ?array
    {
        $argumentValues = $this->resolveArgumentValuesToBeforeRecipe($argumentNodes, $argumentDefaultValueReplacer);
        if ($argumentValues !== $argumentDefaultValueReplacer->getValueBefore()) {
            return null;
        }
        if (\is_string($argumentDefaultValueReplacer->getValueAfter())) {
            $argumentNodes[$argumentDefaultValueReplacer->getPosition()] = $this->normalizeValueToArgument($argumentDefaultValueReplacer->getValueAfter());
            // clear following arguments
            $argumentCountToClear = \count($argumentDefaultValueReplacer->getValueBefore());
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
    private function resolveArgumentValuesToBeforeRecipe(array $argumentNodes, \Rector\Arguments\Contract\ArgumentDefaultValueReplacerInterface $argumentDefaultValueReplacer) : array
    {
        $argumentValues = [];
        /** @var mixed[] $valueBefore */
        $valueBefore = $argumentDefaultValueReplacer->getValueBefore();
        $beforeArgumentCount = \count($valueBefore);
        for ($i = 0; $i < $beforeArgumentCount; ++$i) {
            if (!isset($argumentNodes[$argumentDefaultValueReplacer->getPosition() + $i])) {
                continue;
            }
            $nextArg = $argumentNodes[$argumentDefaultValueReplacer->getPosition() + $i];
            $argumentValues[] = $this->valueResolver->getValue($nextArg->value);
        }
        return $argumentValues;
    }
}
