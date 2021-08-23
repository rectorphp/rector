<?php

declare (strict_types=1);
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
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\FuncCall $node
     */
    public function processReplaces($node, \Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface $replaceArgumentDefaultValue) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            if (!isset($node->params[$replaceArgumentDefaultValue->getPosition()])) {
                return null;
            }
        } elseif (isset($node->args[$replaceArgumentDefaultValue->getPosition()])) {
            $this->processArgs($node, $replaceArgumentDefaultValue);
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $expr
     */
    private function processArgs($expr, \Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface $replaceArgumentDefaultValue) : void
    {
        $position = $replaceArgumentDefaultValue->getPosition();
        $argValue = $this->valueResolver->getValue($expr->args[$position]->value);
        if (\is_scalar($replaceArgumentDefaultValue->getValueBefore()) && $argValue === $replaceArgumentDefaultValue->getValueBefore()) {
            $expr->args[$position] = $this->normalizeValueToArgument($replaceArgumentDefaultValue->getValueAfter());
        } elseif (\is_array($replaceArgumentDefaultValue->getValueBefore())) {
            $newArgs = $this->processArrayReplacement($expr->args, $replaceArgumentDefaultValue);
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
    private function processArrayReplacement(array $argumentNodes, \Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface $replaceArgumentDefaultValue) : ?array
    {
        $argumentValues = $this->resolveArgumentValuesToBeforeRecipe($argumentNodes, $replaceArgumentDefaultValue);
        if ($argumentValues !== $replaceArgumentDefaultValue->getValueBefore()) {
            return null;
        }
        if (\is_string($replaceArgumentDefaultValue->getValueAfter())) {
            $argumentNodes[$replaceArgumentDefaultValue->getPosition()] = $this->normalizeValueToArgument($replaceArgumentDefaultValue->getValueAfter());
            // clear following arguments
            $argumentCountToClear = \count($replaceArgumentDefaultValue->getValueBefore());
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
    private function resolveArgumentValuesToBeforeRecipe(array $argumentNodes, \Rector\Arguments\Contract\ReplaceArgumentDefaultValueInterface $replaceArgumentDefaultValue) : array
    {
        $argumentValues = [];
        /** @var mixed[] $valueBefore */
        $valueBefore = $replaceArgumentDefaultValue->getValueBefore();
        $beforeArgumentCount = \count($valueBefore);
        for ($i = 0; $i < $beforeArgumentCount; ++$i) {
            if (!isset($argumentNodes[$replaceArgumentDefaultValue->getPosition() + $i])) {
                continue;
            }
            $nextArg = $argumentNodes[$replaceArgumentDefaultValue->getPosition() + $i];
            $argumentValues[] = $this->valueResolver->getValue($nextArg->value);
        }
        return $argumentValues;
    }
}
