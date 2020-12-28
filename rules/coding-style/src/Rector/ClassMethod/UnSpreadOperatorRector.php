<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\UnSpreadOperatorRectorTest
 */
final class UnSpreadOperatorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove spread operator', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(...$array)
    {
    }

    public function execute(array $data)
    {
        $this->run(...$data);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $array)
    {
    }

    public function execute(array $data)
    {
        $this->run($data);
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, MethodCall::class];
    }

    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->processUnspreadOperatorClassMethodParams($node);
        }

        return $this->processUnspreadOperatorMethodCallArgs($node);
    }

    private function processUnspreadOperatorClassMethodParams(ClassMethod $classMethod): ?ClassMethod
    {
        $params = $classMethod->params;
        if ($params === []) {
            return null;
        }

        $spreadVariables = $this->getSpreadVariables($args);
        if ($spreadVariables === []) {
            return null;
        }

        return $classMethod;
    }

    private function processUnspreadOperatorMethodCallArgs(MethodCall $methodCall): ?MethodCall
    {
        $args = $methodCall->args;
        if ($args === []) {
            return null;
        }

        $spreadVariables = $this->getSpreadVariables($args);
        if ($spreadVariables === []) {
            return null;
        }

        return $methodCall;
    }

    /**
     * @param Param[]|Args[] $array
     * @return Param[]|Args[]
     */
    private function getSpreadVariables(array $array): array
    {
        $spreadVariables = [];
        foreach ($array as $key => $paramOrArg) {
            if ($paramOrArg instanceof Param && ! $paramOrArg->variadic) {
                continue;
            }

            if ($paramOrArg instanceof Arg && ! $paramOrArg->unpack) {
                continue;
            }

            $spreadVariables[$key] = $paramOrArg;
        }

        return $spreadVariables;
    }
}
