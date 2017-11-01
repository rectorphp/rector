<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeAnalyzer\ClassMethodAnalyzer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\Rector\AbstractRector;

final class ArgumentReplacerRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $argumentChangesMethodAndClass = [];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var mixed[]|null
     */
    private $activeArgumentChangesByPosition;

    /**
     * @var ClassMethodAnalyzer
     */
    private $classMethodAnalyzer;

    /**
     * @var StaticMethodCallAnalyzer
     */
    private $staticMethodCallAnalyzer;

    /**
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(
        array $argumentChangesByMethodAndType,
        MethodCallAnalyzer $methodCallAnalyzer,
        ClassMethodAnalyzer $classMethodAnalyzer,
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer
    ) {
        $this->argumentChangesMethodAndClass = $argumentChangesByMethodAndType;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->classMethodAnalyzer = $classMethodAnalyzer;
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeArgumentChangesByPosition = $this->matchArgumentChanges($node);
        if ($this->activeArgumentChangesByPosition === null) {
            return false;
        }

//        /** @var MethodCall $node */
//        foreach ($this->activeArgumentChangesByPosition as $position => $argumentChange) {
//            $argumentOrParameterCount = $this->countArgumentsOrParameters($node);
//            if ($argumentOrParameterCount < $position + 1) {
//                return true;
//            }
//        }

        return true;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $argumentsOrParameters = $this->getNodeArgumentsOrParameters($node);

        foreach ($this->activeArgumentChangesByPosition as $position => $argumentChange) {
            $key = key($argumentChange);
            $value = array_shift($argumentChange);

            if ($key === '~') {
                if ($value === null) { // remove argument
                    unset($argumentsOrParameters[$position]);
                } else { // new default value
                    $argumentsOrParameters[$position] = BuilderHelpers::normalizeValue($value);
                }
            }
        }

        $this->setNodeArugmentsOrParameters($node, $argumentsOrParameters);

        return $node;
    }

    /**
     * @return mixed[]|null
     */
    private function matchArgumentChanges(Node $node): ?array
    {
        if (! $node instanceof ClassMethod && ! $node instanceof MethodCall && ! $node instanceof StaticCall) {
            return null;
        }

        foreach ($this->argumentChangesMethodAndClass as $type => $argumentChangesByMethod) {
            $methods = array_keys($argumentChangesByMethod);
            if ($this->methodCallAnalyzer->isTypeAndMethods($node, $type, $methods)) {
                return $argumentChangesByMethod[$node->name->toString()];
            }

            if ($this->staticMethodCallAnalyzer->isTypeAndMethods($node, $type, $methods)) {
                return $argumentChangesByMethod[$node->name->toString()];
            }

            if ($this->classMethodAnalyzer->isTypeAndMethods($node, $type, $methods)) {
                return $argumentChangesByMethod[$node->name->toString()];
            }
        }

        return null;
    }

    /**
     * @return mixed[]
     */
    private function getNodeArgumentsOrParameters(Node $node): array
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            return $node->args;
        }

        if ($node instanceof ClassMethod) {
            return $node->params;
        }
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @param mixed[] $argumentsOrParameters
     */
    private function setNodeArugmentsOrParameters(Node $node, array $argumentsOrParameters): void
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $node->args = $argumentsOrParameters;
        }

        if ($node instanceof ClassMethod) {
            $node->params = $argumentsOrParameters;
        }
    }
}
