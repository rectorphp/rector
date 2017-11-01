<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\PrettyPrinter\Standard;
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
     * @var Standard
     */
    private $standard;

    /**
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(
        array $argumentChangesByMethodAndType,
        MethodCallAnalyzer $methodCallAnalyzer,
        ClassMethodAnalyzer $classMethodAnalyzer,
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer,
        Standard $standard
    ) {
        $this->argumentChangesMethodAndClass = $argumentChangesByMethodAndType;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->classMethodAnalyzer = $classMethodAnalyzer;
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
        $this->standard = $standard;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeArgumentChangesByPosition = $this->matchArgumentChanges($node);

        return (bool) $this->activeArgumentChangesByPosition;
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
            } else {
                // replace old value with new one
                $argumentsOrParameter = $argumentsOrParameters[$position];

                // very dummy mechanism just for now
                $currentValue = $this->standard->prettyPrint([$argumentsOrParameter]);
                if (Strings::endsWith($key, $currentValue)) {
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
            if ($this->isTypeAndMethods($node, $type, $methods)) {
                return $argumentChangesByMethod[$node->name->toString()];
            }
        }

        return null;
    }

    /**
     * @return Arg[]|Param[]
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

    /**
     * @param string[] $methods
     */
    private function isTypeAndMethods(Node $node, string $type, array $methods): bool
    {
        if ($this->methodCallAnalyzer->isTypeAndMethods($node, $type, $methods)) {
            return true;
        }

        if ($this->staticMethodCallAnalyzer->isTypeAndMethods($node, $type, $methods)) {
            return true;
        }

        if ($this->classMethodAnalyzer->isTypeAndMethods($node, $type, $methods)) {
            return true;
        }

        return false;
    }
}
